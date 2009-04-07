<?
// qtzrparse.php version 1.1

/* LICENSE:

Copyright (c) 2008 Zephyr Renner
Portions Copyright (c) 2008 Erik Krause
Portions Copyright (c) 2005 Aldo Hoeben

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

The origin of this software must not be misrepresented; you must not claim
that you wrote the original software. If you use this software in a product,
an acknowledgment in the product documentation would be appreciated but is
not required.

Altered source versions must be plainly marked as such, and must not be
misrepresented as being the original software.


THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
IN THE SOFTWARE.

*/

/*
CONTRIBUTORS:

Zephyr Renner (those below did all the work)
Erik Krause
Klaus Reinfeld
Aldo Hoeben
Thomas Rauscher
Ingemar Bergmark
Joost Nieuwenhuijse
*/



// $inc = fopen('panosalado.inc', 'rb');
// if ($inc) {
//   $inc_xml = fread($inc, filesize('panosalado.inc'));
//   fclose($inc);
//   if ($inc_xml) $xml .= $inc_xml;
// }

$filename='PSQTVRParse.php';


$old_error_handler = set_error_handler("myErrorHandler");


// Get quicktime file name
$file = (array_key_exists("mov", $_GET)) ? $_GET["mov"] : "";
// basename() also strips \x00, we don't need to worry about ? and # in path:
// Must be real files anyway, fopen() does not support wildcards
$basename = explode('.', basename($file));
$ext = array_pop($basename);

//myErrorHandler (-1,$ext);

$path=$_SERVER['REQUEST_URI'];
$path=substr($path, 0, strpos($path, $filename));

chdir($_SERVER['DOCUMENT_ROOT'].$path);

if (strcasecmp($ext, "mov") != 0 || !file_exists($file))
{
  myErrorHandler (-1,"File not found or no .mov extension: " . $file,"","");
}

// Open quicktime file for parsing
$fp = fopen($file, "rb");


// Parse the file and output XML or get jpeg tile from file?
$action = (array_key_exists("action", $_GET)) ? $_GET["action"] : "";
$ofsfile = substr($file, 0, strlen($file)-(strlen($ext)+1))."_{$ext}_ps.ofs";
if ($action!="tile") {
  $xmlfile = substr($file, 0, strlen($file)-(strlen($ext)+1))."_{$ext}_ps.xml";
  $cache = (array_key_exists("cache", $_GET)) ? $_GET["cache"] : "true";

  // cache=clear: just remove cache without creating a new file
  if ($cache=="clear") {
    if (file_exists($xmlfile)) {
      unlink($xmlfile);
      myErrorHandler(-1,"Cache removed.","","");
    } else
      myErrorHandler(-1,"Cache not found.","","");
  }

  if (file_exists($xmlfile)) {
    // If cached file is older than mov file, or older than qtzrparse.php file, reset cache
    if ($cache=="true" && ((filemtime($xmlfile) < filemtime($file)) || (filemtime($xmlfile) < getlastmod())) )
      $cache="reset";
  }

  if (file_exists($ofsfile)) {
    // If offsets file does not exist, is older than mov file, or older than qtzrparse.php file, reset cache
    if ($cache=="true" && ((filemtime($ofsfile) < filemtime($file)) || (filemtime($ofsfile) < getlastmod())) )
      $cache="reset";
  }

  if ( (!file_exists($xmlfile)) || (!file_exists($ofsfile)) || ($cache!="true") ) {
    // Parse quicktime file
    $fileindex = 0;
    do {
      fseek($fp, $fileindex);
      $atom = fread($fp,8);
      $atom = unpack("N1size/N1type", $atom);
      $atom["type"] = pack("N", $atom["type"]);

      $fileindex += $atom["size"];
    } while ($atom["type"] != "moov");

    if ($atom["type"] != "moov")
      e ("No 'moov' atom found. Not a Quicktime movie?");

    $moov = fread($fp, $atom["size"]-8);

    $cmovA = GetAtom($moov, "cmov", 0);
    if($cmovA[0] != "")
      myErrorHandler("Compressed movie headers are not supported.");

    $panotrak = "";
    $videtrak = "";
    $pvidetrak = "";
    $trakindex = 0;

    $traks = array();
    $traknr = 0;

    $videtraknr = -1;
    $pvidetraknr = -1;

    do {
      // Traverse all "trak" atoms.
      $trakA = GetAtom($moov, "trak", $trakindex);
      $trak = $trakA[0];
      $trakindex = $trakA[1];

      if ($trak != "") {
        $traknr += 1;

        // See what type of trak this is
        $hdlrA = GetAtom($trak, "hdlr", 0);
        $hdlr = $hdlrA[0];

        if ($hdlr != "") {
          $hdlr = unpack("N1/N1type/N1sub", $hdlr);
          $hdlr["type"] = pack("N", $hdlr["type"]);
          $hdlr["sub"] = pack("N", $hdlr["sub"]);

          if ($hdlr["type"]=="mhlr") {
            $type = $hdlr["sub"];
            if ($type=="pano") {
              $panotrak = $trak[0];

              $stcoA = GetAtom($trak, "stco", 0);
              $stco = unpack("N2/N1offset",$stcoA[0]);
              $pdatoffset = $stco["offset"];

              $stszA = GetAtom($trak, "stsz", 0);
              $stsz = unpack("N1/N1size",$stszA[0]);
              $pdatsize = $stsz["size"];

              $imgtA = GetAtom($trak, "imgt", 0);
              $imgt = unpack("N*", $imgtA[0]);
              if(count($imgt)>2)
                list(,$videtraknr,$pvidetraknr) = $imgt;
              else
                list(,$videtraknr) = $imgt;
            }
          }
        }

        $traks[$traknr] = $trak;

        if ($videtraknr > -1 && $traknr >= $videtraknr && $traknr >= $pvidetraknr) {
          $videtrak = $traks[$videtraknr];
          if($pvidetraknr > -1)
            $pvidetrak = $traks[$pvidetraknr];
          break;
        }
      }

      $trakindex+=4;
    } while ($trak != "");

    // Got the tracks we're looking for?
    if ($panotrak == "" || $videtrak == "")
      myErrorHandler(-1,"Could not find 'pano' and/or 'vide' trak atoms. Not a Quicktime VR panorama?");

    // Is the video track we got in JPEG format?
    $stsdA = GetAtom($videtrak, "stsd", 0);
    $stsd = unpack("N3/N1format/N6/n1width/n1height", $stsdA[0]);
    $stsd["format"] = pack("N" , $stsd["format"]);
    if ($stsd["format"] != "jpeg")
      myErrorHandler(-1,"Only jpeg compressed panoramas supported.");

    // Read panorama description track
    fseek($fp, $pdatoffset);
    $panodat = fread($fp, $pdatsize);

    $pdatA = GetAtom($panodat, "pdat", 0);
    $pdat = $pdatA[0];

    if ($pdat == "")
      myErrorHandler(-1,"Could not find 'pdat' atom. Not a Quicktime VR panorama?");

    $pdat = FlipFloats(unpack("N3/n2ver/N2ref/f9cam/N2imgsize/n2imgframes/N2hotsize/n2hotframes/N1flags/N1type", $pdat));
    $pdat["type"] = pack("N", $pdat["type"]);

    if ($pdat["type"] != "cube")
      myErrorHandler(-1,"Not a cubic panorama. Cylinders are not yet supported.");


    $cuvwA = GetAtom($panodat, "cuvw", $pdat[1]);
    $cuvw = $cuvwA[0];

    if ($cuvw == "")
      myErrorHandler(-1,"Could not find 'cuvw' atom. Invalid cubic panorama.");

    $cuvw = FlipFloats(unpack("N3/f9cam",$cuvw));

    $cufaA = GetAtom($panodat, "cufa", $pdat[1]);
    $cufa = $cufaA[0];

    $tiles = array();
    if ($cufa == "") {
      // Could not find cufa atom. Set 'standard' cubic face data.
      $tiles[] = array("orientation1" => 1 , "orientation2" => 0 , "orientation3" => 0 , "orientation4" => 0, "center1" => 0, "center2" => 0);
      $tiles[] = array("orientation1" => 0.5, "orientation2" => 0 , "orientation3" =>-0.5, "orientation4" => 0, "center1" => 0, "center2" => 0);
      $tiles[] = array("orientation1" => 0 , "orientation2" => 0 , "orientation3" => 1 , "orientation4" => 0, "center1" => 0, "center2" => 0);
      $tiles[] = array("orientation1" => 0.5, "orientation2" => 0 , "orientation3" => 0.5, "orientation4" => 0, "center1" => 0, "center2" => 0);
      $tiles[] = array("orientation1" => 0.5, "orientation2" => 0.5, "orientation3" => 0 , "orientation4" => 0, "center1" => 0, "center2" => 0);
      $tiles[] = array("orientation1" => 0.5, "orientation2" =>-0.5, "orientation3" => 0 , "orientation4" => 0, "center1" => 0, "center2" => 0);
    } else {
      // Get face data from cufa.
      for ($index = 12; $index <= (strlen($cufa)-12); $index+=32) {
        $tiles[] = FlipFloats(unpack("f4orientation/f2center/f1aspect/f1skew", substr($cufa, $index, 32)));
      }
    }

    if (count($tiles) == 0)
      myErrorHandler(-1,"No tiles found in cubic panorama.");

    // Fix invalid values for in pdat atom (generated by Pano2QTVR 0.9.5.3 and older)
    if ((($pdat["imgframes1"] / 4) * $pdat["imgframes2"] * 6) != count($tiles)) {
      $pdat["imgframes1"] = sqrt(count($tiles) / 6) * 4;
      $pdat["imgframes2"] = $pdat["imgframes1"] / 4;
    }


    // Build xml string from Quicktime data
    $stcoA = GetAtom($videtrak, "stco", 0);
    $stszA = GetAtom($videtrak, "stsz", 0);
    $stscA = GetAtom($videtrak, "stsc", 0);

    $stco_num = unpack("N1/N1num", $stcoA[0]);
    $stco_num = $stco_num["num"];
    $stsz_num = unpack("N2/N1num", $stszA[0]);
    $stsz_num = $stsz_num["num"];
    $stsc_num = unpack("N1/N1num", $stscA[0]);
    $stsc_num = $stsc_num["num"];

    $stco = unpack("N2/N".$stco_num."tile", $stcoA[0]);
    $stsz = unpack("N3/N".$stsz_num."tile", $stszA[0]);
    $stsc = unpack("N2/N".($stsc_num * 3)."s2c", $stscA[0]);


    if ($stsz_num != count($tiles))
      myErrorHandler(-1,"Inconsistent number of tiles in tracks.");

    if ($stco_num != $stsz_num) {
      $stsc["s2c" . (1 + $stsc_num * 3)] = $stco_num+1;

      $stsz_index=0;
      $stco_index=0;
      $new_stco = array();

      for($s2c = 0; $s2c < $stsc_num; $s2c++) {
        for($chunk=$stsc["s2c" . (1 + $s2c * 3)]; $chunk<$stsc["s2c" . (4 + $s2c * 3)]; $chunk++) {
          $stco_index++;
          $base = $stco["tile".$stco_index];
          //$base=0;
          $offset=0;

          $c_numtiles = $stsc["s2c" . (2 + $s2c * 3)];
          for($tile = 0; $tile < $c_numtiles; $tile++)
          {
            $stsz_index++;
            $new_stco["tile".$stsz_index] = $base + $offset;
            $offset += $stsz["tile".$stsz_index];
          }
        }
      }

      $stco = $new_stco;
    }


    if($pvidetrak!="") {
      $pstcoA = GetAtom($pvidetrak, "stco", 0);
      $pstszA = GetAtom($pvidetrak, "stsz", 0);
      $pstscA = GetAtom($pvidetrak, "stsc", 0);

      $pstco_num = unpack("N1/N1num", $pstcoA[0]);
      $pstco_num = $pstco_num["num"];
      $pstsz_num = unpack("N2/N1num", $pstszA[0]);
      $pstsz_num = $pstsz_num["num"];
      $pstsc_num = unpack("N1/N1num", $pstscA[0]);
      $pstsc_num = $pstsc_num["num"];


      $pstco = unpack("N2/N".$pstco_num."tile", $pstcoA[0]);
      $pstsz = unpack("N3/N".$pstsz_num."tile", $pstszA[0]);
      $pstsc = unpack("N2/N".($pstsc_num * 3)."s2c", $pstscA[0]);

      if ($pstco_num != $pstsz_num) {
        $pstsc["s2c" . (1 + $pstsc_num * 3)] = $pstco_num+1;

        $pstsz_index=0;
        $pstco_index=0;
        $new_pstco = array();

        for($s2c = 0; $s2c < $pstsc_num; $s2c++) {
          for($chunk=$pstsc["s2c" . (1 + $s2c * 3)]; $chunk<$pstsc["s2c" . (4 + $s2c * 3)]; $chunk++) {
            $pstco_index++;
            $base = $pstco["tile".$pstco_index];
            //$base=0;
            $offset=0;

            $c_numtiles = $pstsc["s2c" . (2 + $s2c * 3)];
            for($tile = 0; $tile < $c_numtiles; $tile++)
            {
              $pstsz_index++;
              $new_pstco["tile".$pstsz_index] = $base + $offset;
              $offset += $pstsz["tile".$pstsz_index];
            }
          }
        }

        $pstco = $new_pstco;
      }
    }

    $JpegOfs = array("front" => array(),
                     "right" => array(),
                     "back"  => array(),
                     "left"  => array(),
                     "up"    => array(),
                     "down"  => array()
                    );
//     $hlookatmin = round($cuvw["cam1"],4);
//     $hlookatmax = round($cuvw["cam2"],4);
//     $vlookatmin = round($cuvw["cam3"],4);
//     $vlookatmax = round($cuvw["cam4"],4);
//     $limitview = ((($hlookatmax - $hlookatmin) < 360) || (($vlookatmax - $vlookatmin) < 180))?"range":"lookat";

//     $xml.= "\t<view "
//         ."hlookat='".-round($cuvw["cam7"],4)."' "
//         ."vlookat='".-round($cuvw["cam8"],4)."' "
//         ."camroll='$camroll' "
//         ."fov='".round($cuvw["cam9"],4)."' "
//         ."fovmin='".round($cuvw["cam5"],4)."' "
//         ."fovmax='".round($cuvw["cam6"],4)."' "
//         ."fisheye='$fisheye' "
//         ."fisheyefovlink='$fisheyefovlink' "
//         ."limitfov='$limitfov' "
//         ."limitview='$limitview' "
//         ."hlookatmin='$hlookatmin' "
//         ."hlookatmax='$hlookatmax' "
//         ."vlookatmin='$vlookatmin' "
//         ."vlookatmax='$vlookatmax' "
//       ."/>\n";
	
	if ( round($cuvw["cam1"],4) == "0" ) { $cameraMinimumPan = ""; } else { $cameraMinimumPan = round($cuvw["cam1"],4); }
	if ( round($cuvw["cam2"],4) == "360" ) { $cameraMaximumPan = ""; } else { $cameraMaximumPan = round($cuvw["cam2"],4); }
	if ( round($cuvw["cam3"],4) == "-90" ) { $cameraMinimumTilt = ""; } else { $cameraMinimumTilt = round($cuvw["cam3"],4); }
	if ( round($cuvw["cam4"],4) == "90" ) { $cameraMaximumTilt = ""; } else { $cameraMaximumTilt = round($cuvw["cam4"],4); }

    //XML start
	$xml = "<?xml version='1.0' encoding='utf-8'?>\n";
//	$xml .= "<PanoSalado>\n";
	
// 	$xml.= "\t<layer id='PanoSalado' url='PanoSalado.swf' depth='0' onStart='loadSpace:".$file."'>\n";
// 	
// 	$xml .= "\t\t<spaces ";
// 	$xml .= "transition='tween:currentSpace.viewport.alpha from 0 over 3 seconds using Expo.easeInOut then do spaces.onTransitionEnd' ";
// 	$xml .= "onTransitionEnd='removeLastSpace' ";
// 	$xml .= "cameraMinimumPan='".$cameraMinimumPan."' ";
// 	$xml .= "cameraMaximumPan='".$cameraMaximumPan."' ";
// 	$xml .= "cameraMinimumTilt='".$cameraMinimumTilt."' ";
// 	$xml .= "cameraMaximumTilt='".$cameraMaximumTilt."' ";
// 	$xml .= "cameraMinimumZoom='".round($cuvw["cam5"]/20,4)."' ";
// 	$xml .= "cameraMaximumZoom='".round($cuvw["cam6"]/20,4)."' ";
// 	$xml .= "cameraPan='".-around($cuvw["cam7"],4)."' ";
// 	$xml .= "cameraTilt='".round($cuvw["cam8"],4)."' ";
// 	$xml .= "cameraZoom='".round($cuvw["cam9"]/20,4)."' ";
// 	$xml .= ">\n";
// 
// 	$xml .= "\t\t\t<space id='".$file."'>\n";
	
	$segments = round ( (15 / count($tiles) / 6), 0);
	if ($segments < 4) { $segments = 4; }

    $hfov = 2 * rad2deg(atan(1 / ($pdat["imgframes1"]/4)));
    $vfov = 2 * rad2deg(atan(1 / $pdat["imgframes2"]));
    $baseindex = 10;
    
    $xml .= "\t\t\t\t<tiledCube \n";
    $xml .= "cameraMinimumPan='".$cameraMinimumPan."' ";
	$xml .= "cameraMaximumPan='".$cameraMaximumPan."' ";
	$xml .= "cameraMinimumTilt='".$cameraMinimumTilt."' ";
	$xml .= "cameraMaximumTilt='".$cameraMaximumTilt."' ";
	$xml .= "cameraMinimumZoom='".round($cuvw["cam5"]/20,4)."' ";
	$xml .= "cameraMaximumZoom='".round($cuvw["cam6"]/20,4)."' ";
	$xml .= "cameraPan='".-round($cuvw["cam7"],4)."' ";
	$xml .= "cameraTilt='".round($cuvw["cam8"],4)."' ";
	$xml .= "cameraZoom='".round($cuvw["cam9"]/20,4)."' ";
    $xml .= ">\n";
    
    for ($panoelem = 0; $panoelem < count($tiles); $panoelem++) {
      // For each tile, add panoelement with inline image node

      $x_ofs = $tiles[$panoelem]["center1"] /2;
      $y_ofs = -$tiles[$panoelem]["center2"] /2;
      $fx_ofs = $tiles[$panoelem]["center1"];
      $fy_ofs = -$tiles[$panoelem]["center2"];
      if ($baseindex > $x_ofs) $baseindex = $x_ofs;
      
      $x_offset = $x_ofs - $baseindex;
      $y_offset = $y_ofs - $baseindex;
      
      $W = $tiles[$panoelem]["orientation1"];
      $X = $tiles[$panoelem]["orientation2"];
      $Y = $tiles[$panoelem]["orientation3"];
      $Z = $tiles[$panoelem]["orientation4"];

      if  ($W != 0 && $X == 0 && $Y == 0) {
//        $pan = "0"; $tilt = "0";
        $face = "front";
      } else if ($W > 0 && $X == 0 && $Y ==-$W) {
//        $pan = "90"; $tilt = "0";
        $face = "right";
      } else if ($W == 0 && $X == 0 && $Y != 0) {
//        $pan = "180"; $tilt = "0";
        $face = "back";
		$rotation = "rotationY='180'";
      } else if ($W != 0 && $X == 0 && $Y == $W) {
//        $pan = "-90"; $tilt = "0";
        $face = "left";
      } else if ($W != 0 && $X == $W && $Y == 0) {
//        $pan = "0"; $tilt = "90";
        $face = "up";
      } else if ($W != 0 && $X ==-$W && $Y == 0) {
//        $pan = "0"; $tilt = "-90";
        $face = "down";
      }

      

      // Referencing this script to extract jpg tiles
      //$imgsrc = "qtzrparse.php/tile.jpg?mov=".$file."&amp;action=tile&amp;count=".$panoelem;
      //$imgsrc.= "&amp;start=".$stco["tile".($panoelem+1)]."&amp;length=".$stsz["tile".($panoelem+1)];
      //$imgsrc = "qtzrparse.php/tile.jpg?mov=".$file."&amp;action=tile&amp;face=".$face."&amp;x_ofs=".$x_ofs."&amp;y_ofs=".$y_ofs;
      $imgsrc = $filename."?mov=".$file."&amp;action=tile&amp;face=".$face."&amp;x_ofs=".$fx_ofs."&amp;y_ofs=".$fy_ofs;

      
      $xml .= "\t\t\t\t\t<tile face='".$face."' x_offset='".$x_offset."' y_offset='".$y_offset."'>../flash/".$imgsrc."</tile>\n";
      
      $JpegOfs[$face][$fx_ofs][$fy_ofs]["start"] = $stco["tile".($panoelem+1)];
      $JpegOfs[$face][$fx_ofs][$fy_ofs]["length"] = $stsz["tile".($panoelem+1)];
/*    // Uncomment to save preview offsets
      if($pvidetrak!="") {
        $JpegOfs[$face][$x_ofs][$y_ofs]["pstart"] = $pstco["tile".($panoelem+1)];
        $JpegOfs[$face][$x_ofs][$y_ofs]["plength"] = $pstsz["tile".($panoelem+1)];
      }
/**/
    } 
    $xml .= "\t\t\t\t</tiledCube>\n";
    
// 	$xml .= "\t\t\t</space>\n";
// 	$xml .= "\t\t</spaces>\n";
// 	$xml .= "\t</layer>\n";
// 	$xml .= "</PanoSalado>";
    //for ($panoelem = 0; $panoelem < count($tiles); $panoelem++)
//     $tiledimagesize = count($JpegOfs["left"]) * $stsd["width"];
//     $xml.= "\t<image type='CUBE' tiled='true' baseindex='$baseindex' tilesize='".$stsd["width"]."' tiledimagewidth='$tiledimagesize' tiledimageheight='$tiledimagesize'>\n";
//     $xml.= "\t\t<left  url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=left&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t\t<front url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=front&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t\t<right url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=right&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t\t<back  url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=back&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t\t<up    url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=up&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t\t<down  url='qtzrparse.php/tile.jpg?mov=$file&amp;action=tile&amp;face=down&amp;x_ofs=%x&amp;y_ofs=%y' />\n";
//     $xml.= "\t</image>\n";
//     $xml.= "</krpano>";

    if($cache=="true" || $cache=="reset") {
      $filepointer = fopen($ofsfile, "wb");
      fwrite($filepointer, serialize($JpegOfs));
      // Cache XML file for next visits
      fclose($filepointer);
      $filepointer = fopen($xmlfile, "wb");
      fwrite($filepointer, $xml);
      fclose($filepointer);
    }

    // Output XML file
    header("Content-type: text/xml");
    header("Content-Length: ".strlen($xml));
    header("Content-Disposition: inline; filename=$xmlfile");
    echo $xml;

  } else {
    // If cached XML file is available, supply that instead of parsing
    header("Location: ".$xmlfile);
  }

} else {
  restore_error_handler();
  $filepointer = @fopen($ofsfile, "rb");
  if ($filepointer) {
    $JpegOfs = unserialize(fread($filepointer, filesize($ofsfile)));
    fclose($filepointer);

    $face = (array_key_exists("face", $_GET)) ? $_GET["face"] : "left";
    $x_ofs = (array_key_exists("x_ofs", $_GET)) ? $_GET["x_ofs"] : "0";
    $y_ofs = (array_key_exists("y_ofs", $_GET)) ? $_GET["y_ofs"] : "0";

    $jpgstart = $JpegOfs[$face][$x_ofs][$y_ofs]["start"];
    $jpglength = $JpegOfs[$face][$x_ofs][$y_ofs]["length"];

    // Getting headers sent by the client.
    //$headers = apache_request_headers();
    $headers = request_headers();
    
    $CacheFileTime = max(filemtime($file), filemtime($ofsfile));
    // Checking if the client is validating his cache and if it is current.
    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $CacheFileTime)) {
        // Client's cache IS current, so we just respond '304 Not Modified'.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $CacheFileTime).' GMT', true, 304);
    } else {
      // Image not cached or cache outdated, we respond '200 OK' and output the image.
      header('Last-Modified: '.gmdate('D, d M Y H:i:s', $CacheFileTime).' GMT', true, 200);
      header("Expires: Mon, 24 Jul 2017 05:00:00 GMT"); // Date in the future
      header("Accept-Ranges: bytes");
      header("Content-Type: image/jpeg");
      header("Content-Length: $jpglength");
      header("Content-Disposition: inline; filename=tile_{$x_ofs}_{$y_ofs}.jpg");

      fseek($fp, $jpgstart);
      echo fread($fp, $jpglength);
    }

  } else {
    $im = @imagecreatetruecolor(250, 250) or die("Cannot Initialize new GD image stream");
    $text_color = imagecolorallocate($im, 255, 255, 255);
    imagestring($im, 5, 5, 5,  "Cannot open", $text_color);
    imagestring($im, 5, 5, 20,  "$ofsfile", $text_color);
    header("Content-Type: image/jpeg");
    header("Content-Disposition: inline");
    imagejpeg($im);
  }
}

fclose($fp);

function myErrorHandler($errno, $errstr, $errfile="", $errline="") 
{
  $ErrTypes = array(E_WARNING           => "Warning",
                    E_NOTICE            => "Notice",
                    E_USER_ERROR        => "User_Error",
                    E_USER_WARNING      => "User_Warning",
                    E_USER_NOTICE       => "User_Notice");
  if ($errno >= 0) //internal 
    $errstr = $ErrTypes[$errno].": ".strip_tags($errstr)." in $errfile on line $errline";
  else //direct call
    $errstr = "Error: ," . $errstr;
   
  $srch = array(',',     "'",'"');
  $repl = array( '',"&apos;","&quot;");
  $errstr = str_replace($srch,$repl,$errstr);
  
  // Output XML file
  header("Content-type: text/xml");
  header("Content-Disposition: inline");
  echo "<krpano onstart=\"error($errstr)\">";
  // echo "<image type='sphere'><sphere url='$errstr'/></image>"; //for KRPano versions < 1.03
  echo "</krpano>";
  exit ();
}

function GetAtom($qt, $atomType, $offset) {
  $atomIndex = strpos($qt, $atomType, $offset);

  if ($atomIndex != FALSE) {
    $atomSize = unpack("N*",substr($qt, $atomIndex - 4, 4));
    $atomSize = $atomSize[1]-4;
    if ($atomSize != 1)
      return array( substr($qt, $atomIndex + 4, $atomSize) , $atomIndex);
  } else {
    return array("",0);
  }
}

function FlipFloats($array) {
  list ($endiantest) = array_values (unpack ('L1L', pack ('V',1)));
  if ($endiantest == 1) {
    for ($item = 0; $item < count($array); $item++) {
      $values = array_values($array);
      $keys = array_keys($array);

      if (is_float($values[$item])) {
        $repacked = unpack("f*", strrev(pack("f", $values[$item])));
        $array[$keys[$item]] = $repacked[1];
      }
    }
  }
  return $array;
}

function request_headers()
{
	if(function_exists("apache_request_headers")) //ÊIfÊapache_request_headers()Êexists...
	{
		if($headers = apache_request_headers()) //ÊAndÊworks...
		{
			return $headers; //ÊUseÊit
		}
	}

	$headers = array();

	foreach(array_keys($_SERVER) as $skey)
	{
		if(substr($skey, 0, 5) == "HTTP_")
		{
			$headername = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($skey, 0, 5)))));
			$headers[$headername] = $_SERVER[$skey];
		}
	}

	return $headers;
}

?>
