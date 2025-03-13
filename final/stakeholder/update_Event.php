<?php
require_once("../includes/authorizeUser.php"); // only stake holder authorized to this page
?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["event_id"];
    $event_desc = trim($_POST["event_desc"]);
    $event_startdate = $_POST["event_startdate"];
    $event_enddate = $_POST["event_enddate"];
    $event_starttime = $_POST["event_starttime"];
    $event_endtime = $_POST["event_endtime"];
    $venue_id = $_POST["type_name"]; //get id
    $event_attendance = trim($_POST["event_attendance"]);

    //image 
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageName = $_FILES['image']['name'];
    $folder = "../assets/upload/image/" . $imageName;
    $isImgEmpty = empty($imageName); 
    if(!empty($event_desc) && !isValid("event_desc", $event_desc)){
        header("Location: updateEventForm?id={$id}&Invalid_description");
        exit(); 
    }

    if(!is_int($event_attendance) && $event_attendance < 0){
        header("Location: updateEventForm?id={$id}&Invalid_attendance");
        exit(); 
    }

    $sql = "UPDATE events SET event_desc = :e_desc, event_startdate = :e_start,
    event_enddate = :e_end, event_starttime=:e_st, event_endtime=:e_et, event_attendance = :e_attendance,
    type_id=:e_type";
    // Add image only if a new one is uploaded
    if (!$isImgEmpty) {
        $sql .= ", image_name = :i_name";
    }
    $sql .= " WHERE event_id = :e_id"; // WHERE condition should be added here
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":e_desc", $event_desc, PDO::PARAM_STR);
    $stmt->bindParam(":e_start", $event_startdate, PDO::PARAM_STR);
    $stmt->bindParam(":e_end", $event_enddate, PDO::PARAM_STR);
    $stmt->bindParam(":e_st", $event_starttime, PDO::PARAM_STR);
    $stmt->bindParam(":e_et", $event_endtime, PDO::PARAM_STR);
    $stmt->bindParam(":e_attendance", $event_attendance, PDO::PARAM_INT);
    $stmt->bindParam(":e_type", $venue_id, PDO::PARAM_INT);
    if(!$isImgEmpty){
        $stmt->bindParam(":i_name", $imageName, PDO::PARAM_STR);
    }
    $stmt->bindParam(":e_id", $id, PDO::PARAM_STR);

    if($stmt->execute()){
        if (!$isImgEmpty) { // after succcessfull insertion, move img to folder
            move_uploaded_file($imageTmpName, $folder);
        }
        sleep(0.5);
        header("Location: updateEventForm?id={$id}&Update_success=1");
        exit();
    } else {
        sleep(0.5);
        header("Location: updateEventForm?id={$id}&Update_error=-1");
        exit();
    }
}
?>

<?php
// Close the connection
$pdo = null;
?>