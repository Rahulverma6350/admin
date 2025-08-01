<?php
session_start();
include("include/db.php");
   // Check kar lo ki dono cheezein mili hain ya nahi
if (isset($_POST['id']) && isset($_POST['type'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];

    // Query decide karo kis type ka user hai
    if ($type === 'user') {
        $sql = "DELETE FROM cart WHERE user_id = ?";
    } elseif ($type === 'session') {
        $sql = "DELETE FROM cart WHERE session_id = ?";
    }

    // Query chalao
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    if( $stmt->execute()){
        echo json_encode(['status' => true]);
    }
    else{
        echo json_encode(['status' => false , "message" => "error occured!"]);
    }
} 
else {
    // Agar id ya type nahi aayi to error
    echo json_encode(['status' => false, 'message' => 'Missing parameters']);
}
?>