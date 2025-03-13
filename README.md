# TourismLethbridgeSite
Requirements:
<br>Python 3.10.11: https://www.python.org/downloads/release/python-31011/
<br>Xampp Server
<br>Add file into htdocs folder after installing files
# Install Guide
To make the Python code to work in your system you need to do the following
<br>Go into your windows powershell or your PC's cmd prompt
<br>Change the directory to your xampp/htdocs server "cd C:\xampp\htdocs" for example
<br>Make sure pip is installed with this command pip --version
<br>If no install is detected do these steps based on your OS
# Windows

<br>run this line to install the dependencies needed to run the python script: pip install pandas mysql-connector-python dateparser spacy validators python-dateutil
<br>When running the software you need to setup certain variables in the code for it to actually run on your computer
# Connecting to the database
There are lines of code you have to change due to how your database may be set up

# Running the Python Script
<br>Your python script may NOT work depending on what command you need to run python
<br>On line 12 of the displayTable.php change this line of code to the command you use to call python programs
<br>$command = "py apptest.py " . escapeshellarg($filePath) . " 2>&1"; // Capture errors
<br>$output = shell_exec($command);
<br>Change "py apptest.py" to the comman your computer uses to call this script
<br>If you have a command prompt or powershell inside the directory where "apptest.py" is located you can try commands to see which one executes the program
<br>Common commands are (py, python, python3)
