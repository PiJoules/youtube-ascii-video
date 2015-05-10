<?php

/* Print all errors if any */
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

ini_set('memory_limit', '-1');

if (isset($_GET["id"])){
	$query = file_get_contents("http://www.youtube.com/get_video_info?video_id=" . $_GET["id"]);
	$video = decodeQueryString($query);
	$video["sources"] = decodeStreamMap($video["url_encoded_fmt_stream_map"]);
	$mp4Source = getSource("video/mp4", "medium", $video["sources"]);
	$data = 'data:video/mp4;base64,' . base64_encode(file_get_contents($mp4Source['url']));
}
else {
	$data = "movie.mp4";
}

function decodeQueryString($queryString) {
    $key; $keyValPair; $keyValPairs; $r; $val; $_i; $_len;
    $r = [];
    $keyValPairs = explode("&", $queryString);
    for ($_i = 0, $_len = count($keyValPairs); $_i < $_len; $_i++) {
        $keyValPair = $keyValPairs[$_i];
        $key = urldecode(explode("=", $keyValPair)[0]);
        $val = urldecode(explode("=", $keyValPair)[1]);
        $r[$key] = $val;
    }
    return $r;
}

function decodeStreamMap($url_encoded_fmt_stream_map) {
    $quality; $sources; $stream; $type; $urlEncodedStream; $_i; $_len; $_ref;
    $sources = [];
    $_ref = explode(",",$url_encoded_fmt_stream_map);
    for ($_i = 0, $_len = count($_ref); $_i < $_len; $_i++) {
        $urlEncodedStream = $_ref[$_i];
        $stream = decodeQueryString($urlEncodedStream);
        $type = explode(";", $stream["type"])[0];
        $quality = explode(",", $stream["quality"])[0];
        $stream["original_url"] = $stream["url"];
        $stream["url"] = "" . $stream["url"];
        if (isset($stream["sig"]))
        	$stream .= "&signature=" . $stream["sig"];
        $sources["" . $type . " " . $quality] = $stream;
    }
    return $sources;
}

function getSource($type, $quality, $sources) {
    $exact; $key; $lowest; $source; $_ref;
    $lowest = null;
    $exact = null;
    $_ref = $sources;
    foreach ($_ref as $key => $source){
        if (strpos($source["type"], $type) !== false){
            if (strpos($source["quality"], $quality) !== false) {
                $exact = $source;
            } else {
                $lowest = $source;
            }
        }
    }
    if ($exact)
    	return $exact;
    return $lowest;
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Jscii</title>
	<style type="text/css">
		html, body {
			font: normal 12px/18px 'helvetica neue' arial;
			color: #333;
		}
		.section > pre { font: bold 8px/5px monospace; color: #000; clear: left; }
		video { width: 200px; }
		.clearfix:after {
			content: ".";
			display: block;
			clear: both;
			visibility: hidden;
			line-height: 0;
			height: 0;
		}
		.clearfix { display: inline-block; }
	</style>
</head>
<body>
	<div class="container">
		<div class="section clearfix">
			<video id="jscii-element-video" controls style="width: 600px;">
				<source src="<?php echo $data; ?>" type='video/mp4' />
				Your browser does not support video
			</video>
			<div>
				<button id="play-video">Start Render</button>
				<button id="pause-video">Pause Render</button>
				<button id="restart-video">Restart Render</button>
				<button id="halfway-video">Go to Halfway Point</button>
			</div>
			<pre id="ascii-container-video"></pre>
		</div>
	</div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="jscii.js"></script>
    <script type="text/javascript" src="youtube-video.js"></script>
	<script>
		var videoJscii = new Jscii({
			container: document.getElementById('ascii-container-video'),
			el: document.getElementById('jscii-element-video'),
			width: 200,
		});

		document.getElementById('play-video').addEventListener('click', function() {
			videoJscii.play();
			document.getElementById('jscii-element-video').play(); 
		});
		document.getElementById('pause-video').addEventListener('click', function() {
			videoJscii.pause();
			document.getElementById('jscii-element-video').pause(); 
		});
		document.getElementById('restart-video').addEventListener('click', function() {
			document.getElementById('jscii-element-video').pause();
			document.getElementById('jscii-element-video').currentTime = 0;
		});
		document.getElementById('halfway-video').addEventListener('click', function() {
			document.getElementById('jscii-element-video').pause();
			console.log(document.getElementById('jscii-element-video').duration);
			document.getElementById('jscii-element-video').currentTime = document.getElementById('jscii-element-video').duration/2;
		});
	</script>
</body>
</html>
