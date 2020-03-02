<?php
function distance($lat1, $lon1, $lat2, $lon2, $earthRadius = 6371000)
{
    // convert from degrees to radians
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    $lonDelta = $lonTo - $lonFrom;
    $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
    $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
    
    $angle = atan2(sqrt($a), $b);
    return $angle * $earthRadius;
}

function get_track($gpx, &$totalpt, &$calpt, $interval) {
    $track = array();
    if(isset($gpx->trk)) {
        foreach ($gpx->trk as $trk) {
            if(isset($trk->trkseg)) {
                foreach($trk->trkseg as $seg){
                    if(isset($seg->trkpt)) {
                        foreach($seg->trkpt as $pt){
                            //$pt["lat"], $pt["lon"];
                            if(isset($pt->time)) {
                                $ut = strtotime($pt->time);
                                $intvl = $interval*60; // minute 2 second
                                $rem   = $ut%$intvl;
                                $idx   = $ut - $rem;
                                $totalpt += 1;
                                if(isset($track[$idx])) {
                                    $prev = $track[$idx]['count'];
                                    $cnt  = $prev + 1;
                                    $track[$idx]['count'] = $cnt;
                                    $nlat = ($track[$idx]['lat']*$prev+$pt['lat'])/$cnt;
                                    $nlon = ($track[$idx]['lon']*$prev+$pt['lon'])/$cnt;
                                    $track[$idx]['lat'] = $nlat;
                                    $track[$idx]['lon'] = $nlon;
                                }
                                else {
                                    $calpt += 1;
                                    $track[$idx] = array();
                                    $track[$idx]['count'] = 1;
                                    $track[$idx]['lat'] = $pt['lat'];
                                    $track[$idx]['lon'] = $pt['lon'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $track;
}

$file_size_limit = 200*1024*1024; // 200 M
$target_dir = "uploads/";
$uploadOk = 0;
$target_file1 = "";
$target_file2 = "";
$distance = 50;
$interval = 1;
if(isset($_FILES["gpxfile1"]) && isset($_FILES["gpxfile2"])) {
    if(isset($_POST['distance'])) {
        $distance = intval($_POST['distance']);
    }
    if(isset($_POST['interval'])) {
        $interval = intval($_POST['interval']);
    }
    $target_file1 = $target_dir . basename($_FILES["gpxfile1"]["name"]);
    $target_file2 = $target_dir . basename($_FILES["gpxfile2"]["name"]);
    $gpxFileType1 = strtolower(pathinfo($target_file1,PATHINFO_EXTENSION));
    $gpxFileType2 = strtolower(pathinfo($target_file2,PATHINFO_EXTENSION));
    // Check if file already exists
    $uploadOk = 1;
    if (file_exists($target_file1)) {
        //echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["gpxfile1"]["size"] > $file_size_limit || $_FILES["gpxfile2"]["size"] > $file_size_limit) {
        //echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    
    if($gpxFileType1 != "gpx" || $gpxFileType2 != "gpx") {
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        // if everything is ok, try to upload file
    } 
    else {
        if (move_uploaded_file($_FILES["gpxfile1"]["tmp_name"], $target_file1) &&
            move_uploaded_file($_FILES["gpxfile2"]["tmp_name"], $target_file2)) {
                //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } 
        else {
                //echo "Sorry, there was an error uploading your file.";
        }
    }
}

$f1 = "Evening_Run.gpx";
$f2 = "20200225194107.gpx";
if($uploadOk == 1) {
    $f1 = $target_file1;
    $f2 = $target_file2;
}

$totalpt1 = 0;
$calpt1   = 0;
$totalpt2 = 0;
$calpt2   = 0;
$trk1 = simplexml_load_file($f1);
$trk2 = simplexml_load_file($f2);
$ttrk1 = get_track($trk1, $totalpt1, $calpt1, $interval);
$ttrk2 = get_track($trk2, $totalpt2, $calpt2, $interval);
?>
<!DOCTYPE html>
<html>
<body>

<h1>計算兩筆 GPX 序列的警示距離</h1>

<h3>上傳兩個 GPX 檔案:</h3>
<form action="parsegpx.php" method="POST" enctype="multipart/form-data">
<label for="distance">選擇警示距離(公尺):</label>
<select id="distance" name="distance">
  <option value="10">10</option>
  <option value="20">20</option>
  <option value="50">50</option>
  <option value="100">100</option>
  <option value="250">250</option>
  <option value="500">500</option>
</select><br><br>
<label for="interval">選擇時間間隔(分鐘):</label>
<select id="interval" name="interval">
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
</select><br><br>
  <label for="myfile1">檔案一:</label>
  <input type="file" id="myfile1" name="gpxfile1"><br><br>
  <label for="myfile1">檔案二:</label>
  <input type="file" id="myfile2" name="gpxfile2"><br><br>
  <input type="submit" value="上傳">
</form>

<?php 
date_default_timezone_set("Asia/Taipei");
$point = 0;
if($calpt1 > 0) {
    foreach($ttrk1 as $t => $pt1) {
        //$ostr = "";
        $tstr = date("Y-m-d H:i", $t);
        $dstr = "unknown";
        if(isset($ttrk2[$t])) {
            $pt2  = $ttrk2[$t];
            $dist = distance($pt1['lat'], $pt1['lon'], $pt2['lat'], $pt2['lon']);
            $dstr = sprintf("%10.2f", $dist);
            //print_r($pt1);
            //print_r($pt2);
            if($dist <= $distance) {
                $point += 1;
                echo $tstr.", 距離 :".$dstr." 公尺<br/>";
            }
        }
    }
}
echo "時間間隔:".$interval."分鐘，警示距離:".$distance."公尺，共有".$point."個時間點被警示<br/>\n";
?>
</body>
</html>
