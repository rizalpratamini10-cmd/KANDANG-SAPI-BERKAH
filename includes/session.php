<?php
// =====================================================
// SESSION HANDLING - FIX ERROR
// =====================================================

// Atur timeout session (30 menit) - SEBELUM session_start()
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>