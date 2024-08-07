<?php
error_reporting(E_ERROR | E_PARSE);
$dir = $argv[1];

// 2024-08-07
// tambah jika ada teks judi online
$kataKataJudiOnline = [
    'slot',
    'gacor',
    'judi'
];

//tambahkan nama fungsi, class, variable yang sering digunakan pada backdoor
//add name of the function, class, variable that is often used on the backdoor
$skripPHP   = [
    'base64_encode',
    'base64_decode',
    'FATHURFREAKZ',
    'eval',
    'gzinflate',
    'str_rot13',
    'convert_uu',
    'shell_data',
    'getimagesize',
    'magicboom',
    'exec',
    'shell_exec',
    'fwrite',
    'str_replace',
    'mail',
    'file_get_contents',
    'url_get_contents',
    'symlink',
    'substr',
    '__file__',
    '__halt_compiler',
];

// $dir = "/var/www/gerbosari-kulonprogo/";

function getDirContents($dir, &$results = array())
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function baca($filenya)
{
    $filesize = filesize($filenya);
    $filesize = round($filesize / 1024 / 1024, 1);
    if ($filesize > 2) { //max 2mb
        $kata = "Skipped--";
        echo $kata;
        /*$fp = fopen('result-scanner.txt', 'a');
                fwrite($fp, $kata);
                fclose($fp);*/
    } else {
        $php_file = file_get_contents($filenya);
        $tokens   = token_get_all($php_file);
        $keluaran = array();
        $batas    = count($tokens);
        if ($batas > 0) {
            for ($i = 0; $i < $batas; $i++) {
                if (isset($tokens[$i][1])) {
                    $keluaran[] .= $tokens[$i][1];
                }
            }
        }
        $keluaran = array_values(array_unique(array_filter(array_map('trim', $keluaran))));
        return ($keluaran);
    }
}

function ngecek($string, $skripPHP)
{

    $keluaran = "";
    foreach ($skripPHP as $value) {
        if ($string) {
            if (in_array($value, $string)) {
                $keluaran .= $value . ", ";
            }
        }
    }
    if ($keluaran != "") {
        $keluaran = substr($keluaran, 0, -2);
    }
    return $keluaran;
}

function arrayContainsWord($str, array $arr)
{
    foreach ($arr as $word) {
        // Works in Hebrew and any other unicode characters
        // Thanks https://medium.com/@shiba1014/regex-word-boundaries-with-unicode-207794f6e7ed
        // Thanks https://www.phpliveregex.com/
        if (preg_match('/(?<=[\s,.:;"\']|^)' . $word . '(?=[\s,.:;"\']|$)/', $str)) return true;
    }
    return false;
}

$list_file = getDirContents($dir);
$jumlah_file_discan = 0;
$jumlah_file_ditemukan = 0;
$jumlah_file_bersih = 0;

$fileDitemukan = [];
$fileAdaGacor = [];

if (!empty($list_file)) {
    foreach ($list_file as $file) {

        if (is_file($file)) {

            if (filesize($file) < 2000000) {
                // cek fungsi digunakan
                $string = baca($file);
                $cek    = ngecek($string, $skripPHP);


                if (empty($cek)) {
                    $kata = date('Y-m-d H:i:s') . " " . $file . " => Safe\n";
                    echo "\033[32m" . $kata;
                    $jumlah_file_bersih++;
                } else if (preg_match("/, /", $cek)) {
                    $kata = date('Y-m-d H:i:s') . " " . $file . " => Found (" . $cek . ")\n";
                    echo "\033[31m" . $kata;
                    $jumlah_file_ditemukan++;

                    $fileDitemukan[] = $kata;
                    $fp = fopen('result-scanner.txt', 'a');
                    fwrite($fp, date('Y-m-d H:i:s') . " " . $kata);
                    fclose($fp);
                } else {
                    $kata = date('Y-m-d H:i:s') . " " . $file . " => Found (" . $cek . ")\n";
                    echo $kata;
                    $jumlah_file_ditemukan++;

                    $fileDitemukan[] = $kata;
                    $fp = fopen('result-scanner.txt', 'a');
                    fwrite($fp, date('Y-m-d H:i:s') . " " . $kata);
                    fclose($fp);
                }

                // cek ada text gacor
                $isiFile = file_get_contents($file);

                if (arrayContainsWord($isiFile, $kataKataJudiOnline)) {
                    echo "\033[31m ADA TEKS SLOT\n";
                    $kata = date('Y-m-d H:i:s') . " " . $file . " => ada teks judi online" . "\n";
                    $jumlah_file_ditemukan++;

                    $fileDitemukan[] = $file;
                    $fp = fopen('result-scanner.txt', 'a');
                    fwrite($fp, $kata);
                    fclose($fp);
                }



                // ob_flush();
                // flush();
                $jumlah_file_discan++;
            } else {
                echo "\033[31m File " . $file . " lebih dari 2MB\n";
            }
        }
    }
}

echo "\n\n";
echo "JUMLAH FILE DISCAN = " . $jumlah_file_discan . "\n";
echo "\033[31mJUMLAH FILE DITEMUKAN = " . $jumlah_file_ditemukan . "\n";
echo "\033[32mJUMLAH FILE BERSIH = " . $jumlah_file_bersih . "\n";
echo "\033[0m";
echo "\n\n";
echo "File ditemukan: " . "\n";
foreach ($fileDitemukan as $k) {
    echo "\033[31m" . $k;
}
echo "\033[0m";

// ob_end_flush();
// echo json_encode($list_file);
