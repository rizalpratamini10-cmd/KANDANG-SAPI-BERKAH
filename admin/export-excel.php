<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulanan';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$minggu = isset($_GET['minggu']) ? (int)$_GET['minggu'] : date('W');

$where = "1=1 AND p.status = 'paid'";

if($filter == 'harian') {
    $where .= " AND DATE(p.created_at) = '$tanggal'";
} elseif($filter == 'mingguan') {
    $start_date = date('Y-m-d', strtotime($tahun . 'W' . str_pad($minggu, 2, '0', STR_PAD_LEFT) . '1'));
    $end_date = date('Y-m-d', strtotime($tahun . 'W' . str_pad($minggu, 2, '0', STR_PAD_LEFT) . '7'));
    $where .= " AND DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif($filter == 'tahunan') {
    $where .= " AND YEAR(p.created_at) = $tahun";
} else {
    $where .= " AND MONTH(p.created_at) = $bulan AND YEAR(p.created_at) = $tahun";
}

$query = "SELECT p.*, c.nama as customer_nama, pr.nama_produk, pr.kode_produk
          FROM pesanan p
          JOIN customers c ON p.id_customer = c.id
          JOIN produk pr ON p.id_produk = pr.id
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_penjualan_$filter.xls");

echo "<table border='1'>";
echo "<tr>
        <th>Tanggal</th>
        <th>Invoice</th>
        <th>Customer</th>
        <th>Produk</th>
        <th>Kode</th>
        <th>Tipe Bayar</th>
        <th>Total</th>
      </tr>";

$total = 0;
while($row = mysqli_fetch_assoc($result)) {
    $total += $row['total_harga'];
    echo "<tr>";
    echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
    echo "<td>" . $row['invoice'] . "</td>";
    echo "<td>" . $row['customer_nama'] . "</td>";
    echo "<td>" . $row['nama_produk'] . "</td>";
    echo "<td>" . $row['kode_produk'] . "</td>";
    echo "<td>" . $row['tipe_pembayaran'] . "</td>";
    echo "<td>" . number_format($row['total_harga'], 0, ',', '.') . "</td>";
    echo "</tr>";
}

echo "<tr><td colspan='6' align='right'><strong>TOTAL</strong></td><td><strong>Rp " . number_format($total, 0, ',', '.') . "</strong></td></tr>";
echo "</table>";
?>