<?php
require_once __DIR__ . '/vendor/autoload.php';
include 'koneksi.php';

// Konfigurasi mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'L', // Landscape untuk tabel lebih lebar
    'margin_top' => 40,
    'margin_bottom' => 25,
    'margin_left' => 15,
    'margin_right' => 15
]);

// Header & Footer
$mpdf->SetHTMLHeader('
<table width="100%" style="border-bottom: 1px solid #000; padding-bottom: 10px;">
    <tr>
        <td width="20%" style="text-align: left;">
            <img src="logo.png" style="width: 50px;" />
        </td>
        <td width="60%" style="text-align: center;">
            <h2 style="margin:0; padding:0;">PONDOK PESANTREN</h2>
            <p style="margin:0; padding:0; font-size:12px;">Jl. Contoh No. 123, Kota</p>
        </td>
        <td width="20%" style="text-align: right; font-size:11px;">
            Tanggal: ' . date('d-m-Y') . '
        </td>
    </tr>
</table>
');

$mpdf->SetHTMLFooter('
<table width="100%" style="border-top: 1px solid #000; padding-top: 5px; font-size: 10px;">
    <tr>
        <td width="33%">Dicetak: ' . date('d-m-Y H:i:s') . '</td>
        <td width="33%" align="center">Halaman {PAGENO} dari {nbpg}</td>
        <td width="33%" align="right">&copy; ' . date('Y') . ' Pondok Pesantren</td>
    </tr>
</table>
');

// CSS untuk styling PDF
$css = '
body { 
    font-family: Arial, sans-serif;
    font-size: 11px;
}
h2 { 
    text-align: center;
    color: #0066ff;
    margin-bottom: 20px;
    font-size: 18px;
}
table.data {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
table.data th {
    background-color: #0066ff;
    color: white;
    padding: 10px 8px;
    font-weight: bold;
    border: 1px solid #0066ff;
    text-align: center;
}
table.data td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}
table.data tr:nth-child(even) {
    background-color: #f9f9f9;
}
table.data tr:hover {
    background-color: #f0f0f0;
}
.total {
    margin-top: 15px;
    font-weight: bold;
    text-align: right;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 5px;
}
';

// Query data santri
$query = mysqli_query($koneksi, "SELECT * FROM santri ORDER BY id ASC");
$total_santri = mysqli_num_rows($query);

// HTML Content
$html = '<h2>LAPORAN DATA SANTRI</h2>';

$html .= '<table class="data">';
$html .= '
<thead>
    <tr>
        <th width="5%">No</th>
        <th width="25%">Nama Santri</th>
        <th width="15%">Tempat Lahir</th>
        <th width="12%">Tanggal Lahir</th>
        <th width="10%">Kelas</th>
        <th width="10%">Nilai Ujian</th>
    </tr>
</thead>
<tbody>
';

$no = 1;
$total_nilai = 0;

while ($row = mysqli_fetch_assoc($query)) {
    // Format tanggal
    $tgl_lahir = date('d-m-Y', strtotime($row['tanggal_lahir']));
    
    $html .= "
    <tr>
        <td>$no</td>
        <td style='text-align:left; padding-left:10px;'>{$row['nama_santri']}</td>
        <td>{$row['tempat_lahir']}</td>
        <td>$tgl_lahir</td>
        <td>{$row['kelas']}</td>
        <td>{$row['nilai_ujian']}</td>
    </tr>";
    
    $total_nilai += $row['nilai_ujian'];
    $no++;
}

$rata_rata = $total_santri > 0 ? number_format($total_nilai / $total_santri, 2) : 0;

$html .= '</tbody></table>';

$html .= "
<div class='total'>
    <p>Total Santri: <strong>$total_santri</strong> orang</p>
    <p>Rata-rata Nilai: <strong>$rata_rata</strong></p>
</div>
";

// Render PDF
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

// Output PDF
// 'I' = tampil di browser, 'D' = download
$mpdf->Output('Laporan_Data_Santri_' . date('Y-m-d') . '.pdf', 'I');
?>