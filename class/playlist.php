<?php

/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 3-5-2016
 * Time: 20:59
 */
class Playlist
{

    var $file;
    var $folder;

    function remove_linebreaks($string){
        return preg_replace( "/\r|\n/", "", $string);
    }

    function __construct($filename, $folder)
    {
        $this->file = $filename;
        $this->folder = dirname(urldecode($folder));
    }

    function openPLS(){
        $items = parse_ini_file($this->file, true, INI_SCANNER_RAW);
        //var_dump($items);
        $fileArray = array();
        for($i = 1; $i <= $items["playlist"]["NumberOfEntries"]; $i++){
            $fullPath = $this->remove_linebreaks(str_replace("http://dummy", "", url_to_absolute("http://dummy" . $this->folder . "/", $items["playlist"]["File" . $i])));
            $fullPath = str_replace(' ', '%20', $fullPath);
            $fullPath = urlencode($fullPath);
            $nameOnly = $this->remove_linebreaks($items["playlist"]["File" . $i]);
            //$nameOnly = str_replace(' ', '%20', $nameOnly);
            $nameOnly = basename($nameOnly);
            $nameOnly = urlencode($nameOnly);
            $fileArray[] = array($fullPath, $nameOnly);
        }
        return $fileArray;
    }
    
    function savePLS($jsonPlaylist){
        global $client;
        $decodedPlaylist = json_decode($jsonPlaylist, true);
        $plsPlaylist = "[playlist]\n";
        $i = 0;
        foreach ($decodedPlaylist as $item){
            $i++;
            $file = $item["mp3"];
            $file = strstr($file, "get_file.php?file=");
            $file = str_replace("get_file.php?file=", "", $file);
            $file = $this->getRelativePath(urldecode($this->file), urldecode($file));

            $plsPlaylist .= "File" . $i . "=" . $file . "\n";
        }
        $plsPlaylist .= "NumberOfEntries=" . $i;

        //echo $plsPlaylist;
        return $client->request('PUT', $this->file, $plsPlaylist);
    }

    function openM3U(){
        $handle = fopen($this->file, "r");
        $fileArray = array();
        while (($line = fgets($handle)) !== false) {
            if($line == ""){
                continue;
            }
            if(starts_with($line, "#EXT")){
                continue;
            }
            $fullPath = (str_replace("http://dummy", "", url_to_absolute("http://dummy" . $this->folder . "/", $this->remove_linebreaks($line))));
            $fullPath = str_replace(' ', '%20', $fullPath);
            $fullPath = urlencode($fullPath);
            $nameOnly = $this->remove_linebreaks($line);
            //$nameOnly = str_replace(' ', '%20', $nameOnly);
            $nameOnly = basename($nameOnly);
            $nameOnly = urlencode($nameOnly);
            $fileArray[] = array($fullPath, $nameOnly);
        }
        fclose($handle);
        return $fileArray;
    }

    function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}