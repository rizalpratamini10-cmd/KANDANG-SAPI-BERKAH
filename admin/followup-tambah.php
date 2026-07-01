<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$tipe = isset($_POST['tipe']) ? mysqli_real_escape_string($conn, $_POST['tipe']) : '';
$id_target = isset($_POST['id_target']) ? (int)$_POST['id_target'] : 0;
$catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
$admin_id = $_SESSION['admin_id'];

// Cek apakah request dari AJAX (ada header)
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if($tipe && $id_target && $catatan) {
    $query = "INSERT INTO follow_up (tipe_followup, id_target, catatan, followup_date, created_by) 
              VALUES ('$tipe', '$id_target', '$catatan', CURDATE(), '$admin_id')";
    
    if(mysqli_query($conn, $query)) {
        // Jika AJAX, return response JSON
        if($is_ajax) {
            echo "success";
            exit;
        }
        
        // Redirect back untuk request biasa
        if(isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: followup.php");
        }
        exit;
    } else {
        if($is_ajax) {
            echo "failed: " . mysqli_error($conn);
            exit;
        }
        header("Location: followup.php");
        exit;
    }
} else {
    if($is_ajax) {
        echo "failed: data tidak lengkap";
        exit;
    }
    header("Location: followup.php");
    exit;
}
?>