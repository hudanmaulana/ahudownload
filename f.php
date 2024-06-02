
<?php
require_once('vendor/autoload.php');

function getBillID($lastID = 0, $lastTs='', $time='')
{
    $data = "lastID={$lastID}&ygdicari=0&cari=&time={$time}&his=1&lastTs={$lastTs}";
    $link = "https://fidusia.ahu.go.id/app/nextPageDaftar.php";
    $c = curl_init($link);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0");
    curl_setopt($c, CURLOPT_ENCODING, "gzip, deflate, br");
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_HEADER, 1);
    curl_setopt($c, CURLOPT_COOKIE, file_get_contents("cookie2.txt"));
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_setopt($c, CURLOPT_POST, true);
    $response = curl_exec($c);
    $httpcode = curl_getinfo($c);
    
    if(!$httpcode) return false; 
    else
    {
        $header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
        $body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    }

    $a = json_decode($body, true);

    return $a;
}

function getCert($id, $nomor_kontrak)
{
    if (!file_exists(dirname(__FILE__) . "/file/{$nomor_kontrak}_Sertifikat_Pendaftaran_Fidusia.pdf"))
    {
        $ch = curl_init();
        $source = "https://fidusia.ahu.go.id/app/form_cetak_sertifikat_pdf.php?id={$id}";
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIE, file_get_contents("cookie2.txt"));
        $data = curl_exec($ch);
        curl_close($ch);

        // Nama file baru dengan menggunakan nomor kontrak
        $nomor_kontrak_cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $nomor_kontrak);
		$nomor_kontrak_cleaned = preg_replace('/\s+/', '_', $nomor_kontrak_cleaned);
		$destination = dirname(__FILE__) . "/file/{$nomor_kontrak_cleaned}_Sertifikat_Pendaftaran_Fidusia.pdf"; // Ubah penamaan file
        $file = fopen($destination, "wb");
        fputs($file, $data);
        fclose($file);

        echo "Sertifikat kontrak {$nomor_kontrak} berhasil diunduh." . PHP_EOL;
    }
}

// function getProof($id, $nomor_kontrak)
// {
//     if(!file_exists(dirname(__FILE__) . "/file/[{$nomor_kontrak}]_Bukti_Pemesanan_Voucher.pdf")) // Ubah penamaan file
//     {
//         $ch = curl_init();
//         $source = "https://fidusia.ahu.go.id/app/form_cetak.php?id={$id}&bukti=cetak";
//         curl_setopt($ch, CURLOPT_URL, $source);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//         curl_setopt($ch, CURLOPT_COOKIE, file_get_contents("cookie2.txt"));
//         $data = curl_exec($ch);
//         curl_close($ch);
// 		$nomor_kontrak_cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $nomor_kontrak);
// 		$nomor_kontrak_cleaned = preg_replace('/\s+/', '_', $nomor_kontrak_cleaned);
//         $destination = dirname(__FILE__) . "/file/{$nomor_kontrak_cleaned}_Bukti_Pemesanan_Voucher.pdf"; // Ubah penamaan file
//         $file = fopen($destination, "wb");
//         fputs($file, $data);
//         fclose($file);

//         echo "Bukti pemesanan voucher untuk nomor kontrak {$nomor_kontrak} berhasil diunduh." . PHP_EOL;
//     }
// }
	

	function save($filename, $content)
	{
	    $save = fopen($filename, "a");
	    fputs($save, "$content".PHP_EOL);
	    fclose($save);
	}

	echo "===================================================================".PHP_EOL;
echo "Isikan nama file. (file harus berada dalam 1 folder yang sama dengan program)".PHP_EOL;
echo "===================================================================".PHP_EOL.PHP_EOL.PHP_EOL;
echo "-> Nama file : ";
$file = rtrim(fgets(STDIN));

if(file_exists($file))
{
    $bill = trim(file_get_contents($file));
    $bill2 = explode(PHP_EOL, $bill);

	// Baca isi file nokontrak.txt
    $nokontrakFile = 'nokontrak.txt';
    if (file_exists($nokontrakFile)) {
        $nomorKontrak = explode(PHP_EOL, trim(file_get_contents($nokontrakFile)));
    } else {
        die("File nokontrak.txt tidak ditemukan.");
    }

    for ($i = 0; $i < count($bill2); $i++) {
        echo $i . "/" . count($bill2) . ". Nomor bill id => " . $bill2[$i] . PHP_EOL . PHP_EOL;

        // Periksa jika nomor kontrak tidak kosong
        if (!empty($nomorKontrak[$i])) {
            getCert($bill2[$i], $nomorKontrak[$i]);
            // getProof($bill2[$i], $nomorKontrak[$i]);
          
            sleep(1);
        } else {
            echo "Nomor kontrak tidak ditemukan dari file nokontrak.txt" . PHP_EOL;
        }
    }
}
else
{
    die("File {$file} tidak ditemukan.");
}
?>