<?php 
error_reporting(E_ERROR | E_PARSE);
$dir = $argv[1];

function getDirContents($dir, &$results = array()) {
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
function ngelist($dir, &$keluaran = array()) {
    $scan = scandir($dir);
    foreach ($scan as $key => $value) {
        $lokasi = $dir . DIRECTORY_SEPARATOR . $value;
        if (!is_dir($lokasi)) {
            $keluaran[] = $lokasi;
        } else if ($value != "." && $value != "..") {
            ngelist($lokasi, $keluaran);
            $keluaran[] = $lokasi;
        }
    }
    return $keluaran;
}
function baca($filenya) {
        $filesize = filesize($filenya);
        $filesize = round($filesize / 1024 / 1024, 1);
        if($filesize>2) { //max 2mb
                $kata = "Skipped--";
                echo $kata;
                /*$fp = fopen('result-scanner.txt', 'a');
                fwrite($fp, $kata);
                fclose($fp);*/
        }else {
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
function ngecek($string) {
    //tambahkan nama fungsi, class, variable yang sering digunakan pada backdoor
    //add name of the function, class, variable that is often used on the backdoor
    $dicari   = array(
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
        '__halt_compiler'
    );
    $keluaran = "";
    foreach ($dicari as $value) {
        if (in_array($value, $string)) {
            $keluaran .= $value . ", ";
        }
    }
    if ($keluaran != "") {
        $keluaran = substr($keluaran, 0, -2);
    }
    return $keluaran;
}


$list_file = getDirContents($dir);
$jumlah_file_discan = 0;
$jumlah_file_ditemukan = 0;
$jumlah_file_bersih = 0;

if (!empty($list_file)) {
    foreach ($list_file as $file) {
        
        if (is_file($file)) {
            $string = baca($file);
            $cek    = ngecek($string);
            if (empty($cek)) {
                $kata = $file ." => Safe\n";
                echo $kata;
                $jumlah_file_bersih++;
            } else if(preg_match("/, /", $cek)) {
                $kata = $file ." => Found (". $cek .")\n";
                echo $kata;
                $jumlah_file_ditemukan++;
                $fp = fopen('result-scanner.txt', 'a');
                fwrite($fp, $kata);
                fclose($fp);
            }else{
                $kata = $file ." => Found (". $cek .")\n";
                echo $kata;
                $jumlah_file_ditemukan++;
            }
            $jumlah_file_discan++;
        }
    }
}

echo "\n\n";
echo "JUMLAH FILE DISCAN = ".$jumlah_file_discan."\n";
echo "\033[31mJUMLAH FILE DITEMUKAN = ".$jumlah_file_ditemukan."\n";
echo "\033[32mJUMLAH FILE BERSIH = ".$jumlah_file_bersih."\n";
echo "\033[0m";
echo "\n\n";

?>
