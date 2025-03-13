import os
import re
import pandas as pd
import mysql.connector
from mysql.connector import Error
import dateparser
import string
import spacy
import html
import validators
import logging
from dateutil import parser
from dateutil.rrule import rrule, WEEKLY, MO, TU, WE, TH, FR, SA, SU
from datetime import datetime

# Load spaCy model for text processing
nlp = spacy.load('en_core_web_lg')

# MySQL connection configuration
db_config = {
    'user': 'root',  # Your MySQL username
    'password': 'Sixmile43drive',  # Your MySQL password
    'host': 'localhost',  # Your MySQL host
    'database': 'testdb',  # Your database name
}

# Configure logging
logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

# Helper function to check if file is a CSV
def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in {'csv'}

# Helper function to remove trailing commas
def remove_trailing_commas(text):
    if pd.isna(text):
        return text
    return str(text).rstrip(',')

# Helper function to check for redundancy using spaCy
def is_redundant(text1, text2):
    text1 = text1.strip().lower()
    text2 = text2.strip().lower()
    text1 = text1.translate(str.maketrans('', '', string.punctuation))
    text2 = text2.translate(str.maketrans('', '', string.punctuation))
    
    # Exact match check
    if text1 == text2:
        return True
    
    # spaCy similarity check
    doc1 = nlp(text1)
    doc2 = nlp(text2)
    similarity = doc1.similarity(doc2)
    return similarity > 0.75  # Adjusted threshold for better accuracy

# Helper function to connect to MySQL database
def connect_to_db():
    try:
        connection = mysql.connector.connect(**db_config)
        logging.info("Database connection successful")
        return connection
    except Error as e:
        logging.error(f"Error connecting to MySQL: {e}")
        return None

# Function to parse date using dateparser
def parse_date(date_str):
    if pd.isna(date_str):  # Check if the date is missing (NaN)
        return None  # Return None for NULL in SQL
    
    # Clean the date string (remove trailing commas and extra spaces)
    date_str = remove_trailing_commas(date_str).strip()
    
    # Check if the string contains any recognizable date patterns
    if not re.search(r'\d{1,2}[/-]\d{1,2}[/-]\d{2,4}', date_str) and not re.search(r'[a-zA-Z]{3}\.?\s*\d{1,2}', date_str):
        # If no date pattern is found, return the current date
        return datetime.now().date()
    
    try:
        # Try parsing with dateutil.parser (fuzzy parsing)
        parsed_date = parser.parse(date_str, fuzzy=True)
        return parsed_date.date()  # Return only the date part
    except ValueError:
        # Log the invalid date for debugging
        logging.warning(f"Invalid date format: {date_str}")
        return datetime.now().date()  # Return the current date if parsing fails

# Function to clean description
def clean_description(description):
    if pd.isna(description):
        return 'N/A'
    # Decode HTML entities and replace special characters
    return html.unescape(description).replace('…', '...').replace('&rsquo;', "'").replace('&amp;', '&')

# Function to validate URL
def validate_url(url):
    if pd.isna(url):
        return 'N/A'
    if validators.url(url):  # Check if the URL is valid
        return url
    else:
        logging.warning(f"Invalid URL: {url}")
        return 'N/A'  # Mark invalid URLs as 'N/A'

# Function to validate field
def validate_field(value, field_name, default='N/A'):
    if pd.isna(value) or value.strip() == '' or value.strip().lower() == 'n/a':
        logging.warning(f"Missing or invalid {field_name}: {value}. Using default: {default}")
        return default
    return value

# Function to parse recurring dates
def parse_recurring_dates(text):
    # Initialize variables
    start_date = None
    end_date = None
    recurrence = None
    exceptions = []

    # Extract recurrence pattern (e.g., "Every Saturday")
    if 'every' in text.lower():
        recurrence = text.lower().split('every')[1].split('until')[0].strip()

    # Extract the date range (e.g., "until Oct. 12")
    if 'until' in text.lower():
        date_range = text.lower().split('until')[1].strip()
        # Extract the end date (e.g., "Oct. 12")
        end_date_str = re.search(r'[a-zA-Z]{3}\.?\s*\d{1,2}', date_range)
        if end_date_str:
            end_date = parse_date(end_date_str.group())

    # Extract exceptions (e.g., "no market Aug. 24")
    if 'no market' in text.lower():
        exception_text = text.lower().split('no market')[1].strip()
        # Extract the exception date (e.g., "Aug. 24")
        exception_date = re.search(r'[a-zA-Z]{3}\.?\s*\d{1,2}', exception_text)
        if exception_date:
            exceptions.append(parse_date(exception_date.group()))

    # Set start_date to the next occurrence of the recurrence day
    if recurrence and end_date:
        today = datetime.now()
        # Map recurrence to a weekday
        recurrence_map = {
            'saturday': SA,
            'sunday': SU,
            'monday': MO,
            'tuesday': TU,
            'wednesday': WE,
            'thursday': TH,
            'friday': FR
        }
        weekday = recurrence_map.get(recurrence.lower(), SA)  # Default to Saturday
        # Find the next occurrence of the weekday
        start_date = rrule(WEEKLY, dtstart=today, byweekday=weekday, count=1)[0]

    return {
        'start_date': start_date,
        'end_date': end_date,
        'recurrence': recurrence,
        'exceptions': exceptions
    }

# Function to generate recurring dates
def generate_recurring_dates(start_date, end_date, recurrence, exceptions):
    # Map recurrence to rrule frequency
    recurrence_map = {
        'saturday': SA,
        'sunday': SU,
        'monday': MO,
        'tuesday': TU,
        'wednesday': WE,
        'thursday': TH,
        'friday': FR
    }
    
    # Generate recurring dates
    if start_date and end_date and recurrence:
        weekday = recurrence_map.get(recurrence.lower(), SA)  # Default to Saturday
        dates = list(rrule(
            WEEKLY,
            dtstart=start_date,
            until=end_date,
            byweekday=weekday
        ))
        
        # Remove exceptions
        dates = [date for date in dates if date not in exceptions]
        
        return dates
    else:
        return []

def is_redundant(event, db_data, similarity_threshold=0.9):
    """
    Check if an event is redundant by comparing it with existing database entries using spaCy.
    """
    event_doc = nlp(event['eventDescription'])  # Compare descriptions for redundancy
    for db_row in db_data:
        db_description = db_row[2]  # Assuming eventDescription is at index 2
        db_doc = nlp(db_description)
        similarity = event_doc.similarity(db_doc)
        if similarity >= similarity_threshold:
            return True  # Event is redundant
    return False  # Event is not redundant

def process_csv(file_path):
    """Process the CSV file and insert data into the database."""
    # Read the CSV file using pandas with UTF-8 encoding
    try:
        df = pd.read_csv(file_path, encoding='utf-8')
        logging.info("CSV file read successfully")
        logging.debug(f"CSV Data:\n{df.head()}")  # Debug: Print the first few rows of the CSV
    except Exception as e:
        logging.error(f"Failed to read CSV file: {e}")
        return

    # Connect to the MySQL database
    connection = connect_to_db()
    if not connection:
        logging.error("Failed to connect to the database.")
        return

    cursor = connection.cursor()
    cursor.execute("SELECT eventSource, Title, eventDescription, location, eventdate, moredetails FROM data_table")
    db_data = cursor.fetchall()

    # Prepare data for batch insert
    data_to_insert = []
    skipped_rows = []

    for index, row in df.iterrows():
        eventSource = validate_field(row['Source'], 'Source')
        Title = validate_field(row['Title'], 'Title')
        eventDescription = validate_field(row['Description'], 'Description', default='No description provided')
        location = validate_field(remove_trailing_commas(row['Location']), 'Location', default='Unknown')
        eventdate = parse_date(row['Date'])  # Use the parse_date function
        moredetails = validate_url(row['MoreDetails'])

        # Debug: Log the validated data
        logging.debug(f"Row {index} - Validated Data: Source={eventSource}, Title={Title}, Description={eventDescription}, Location={location}, Date={eventdate}, MoreDetails={moredetails}")

        # Check if the event is redundant using spaCy
        event_data = {
            'eventSource': eventSource,
            'Title': Title,
            'eventDescription': eventDescription,
            'location': location,
            'eventdate': eventdate,
            'moredetails': moredetails
        }
        redundant = 'Yes' if is_redundant(event_data, db_data) else 'No'

        # Handle recurring events
        if 'every' in str(row['Date']).lower():
            # Parse recurring dates
            recurring_info = parse_recurring_dates(str(row['Date']))
            if recurring_info['start_date'] and recurring_info['end_date'] and recurring_info['recurrence']:
                # Generate all recurring dates
                recurring_dates = generate_recurring_dates(
                    recurring_info['start_date'],
                    recurring_info['end_date'],
                    recurring_info['recurrence'],
                    recurring_info['exceptions']
                )
                # Insert each recurring date as a separate entry
                for date in recurring_dates:
                    data_to_insert.append((
                        eventSource,
                        Title,
                        eventDescription,
                        location,
                        date,  # Use the generated recurring date
                        moredetails,
                        redundant
                    ))
            continue  # Skip the original row for recurring events

        # Handle single-date events
        if eventSource == 'N/A' or Title == 'N/A' or eventDescription == 'N/A' or location == 'N/A' or eventdate is None or moredetails == 'N/A':
            skipped_rows.append({
                'index': index,
                'row_data': {
                    'Source': eventSource,
                    'Title': Title,
                    'Description': eventDescription,
                    'Location': location,
                    'Date': row['Date'],  # Original date string
                    'MoreDetails': moredetails
                },
                'reason': {
                    'Source': 'Missing' if eventSource == 'N/A' else 'Valid',
                    'Title': 'Missing' if Title == 'N/A' else 'Valid',
                    'Description': 'Missing' if eventDescription == 'N/A' else 'Valid',
                    'Location': 'Missing' if location == 'N/A' else 'Valid',
                    'Date': 'Invalid' if eventdate is None else 'Valid',
                    'MoreDetails': 'Invalid' if moredetails == 'N/A' else 'Valid'
                }
            })
            continue  # Skip this row

        # Add the row to the list for batch insert
        data_to_insert.append((eventSource, Title, eventDescription, location, eventdate, moredetails, redundant))

    # Batch insert
    if data_to_insert:
        insert_query = """
            INSERT INTO data_table (eventSource, Title, eventDescription, location, eventdate, moredetails, redundant)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        try:
            cursor.executemany(insert_query, data_to_insert)
            connection.commit()
            logging.info(f"Inserted {len(data_to_insert)} rows into the database.")
            logging.debug("Rows inserted:")
            for row in data_to_insert:
                logging.debug(row)
        except Exception as e:
            logging.error(f"Failed to insert data into the database: {e}")
    else:
        logging.warning("No valid rows to insert.")

    # Log skipped rows
    if skipped_rows:
        logging.warning(f"Skipped {len(skipped_rows)} rows due to missing or invalid data:")
        for skipped_row in skipped_rows:
            logging.warning(f"Row {skipped_row['index']}:")
            logging.warning(f"Data: {skipped_row['row_data']}")
            logging.warning(f"Reason: {skipped_row['reason']}")

    # Print results for PHP to parse
    print("=== MODIFIED DATA ===")
    for row in data_to_insert:
        print(f"EventSource: {row[0]}, Title: {row[1]}, Description: {row[2]}, Location: {row[3]}, Date: {row[4]}, MoreDetails: {row[5]}, Redundant: {row[6]}")

    print("=== SKIPPED ROWS ===")
    for skipped_row in skipped_rows:
        print(f"Row {skipped_row['index']}:")
        print(f"Data: {skipped_row['row_data']}")
        print(f"Reason: {skipped_row['reason']}")

    cursor.close()
    connection.close()

if __name__ == '__main__':
    import sys
    if len(sys.argv) != 2:
        print("Usage: python apptest.py <file_path>")
        sys.exit(1)

    file_path = sys.argv[1]
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        sys.exit(1)

    process_csv(file_path)