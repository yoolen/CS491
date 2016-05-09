<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['creds']) or $_SESSION['creds'] <= 0) {
    header("Location: login.php");
}

require_once "compilation/classes.php";
require_once "compilation/helper.php";
if (isset($_POST['code'])) {
    $code = $_POST['code'];
    $lang = $_POST['language'];
    $inputs = $_POST['inputs'];
    $req = new Request($lang, $code, $inputs);
    $request = json_encode($req);
    $curlRequest = curl_init('http://cs490.iidcct.com/comp/evaluate.php');
    curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $request);
    curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlRequest, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($request))
    );

    $result = json_decode(curl_exec($curlRequest));
    curl_close($curlRequest);
    $outBox = $result->output;
    //print_r($result);
}
?>
<html>
    <head>
        <title>NJIT High School Programming Contest</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
        <script src="library/ace/ace.js" type="text/javascript" charset="utf-8"></script>
        <style>
            body {
                width:100%;
                height:100%;
                margin: 0;
                padding: 0;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'Open Sans', sans-serif;
            }
            p, div {
                font-family: 'Open Sans', sans-serif;
            }

            .center {
                border: 1px solid black;
            }

            .center:after {
                content: '';
                display: table;
                clear: both;
            }
            #hints {
                font: inherit !important;
                background-color: lightgray;
                float: right;
                height:70vh;
                width: 15%;
            }
            #editor {
                width:85%;                
                height:70vh;
                float: left;
                overflow-y: hidden;
                font-style: normal;
                font-variant: normal;
                font-weight: normal;
                font-stretch: normal;
                font-size: 12px;
                line-height: normal;
                font-family: Monaco, Menlo, 'Ubuntu Mono', Consolas, source-code-pro, monospace;
            }
            #editor div {
                font: inherit !important;
            }

            #dragbar{
                background-color:black;
                height:100%;
                float: left;
                width: 3px;
                cursor: col-resize;
            }
            #ghostbar{
                width:3px;
                background-color:#000;
                opacity:0.5;
                position:absolute;
                cursor: col-resize;
                z-index:999;
            }

            .head {
                height: 8vh;
                background: linear-gradient(to bottom, rgba(178,3,12,1) 1%,rgba(143,2,34,1) 91%,rgba(109,0,25,1) 100%);
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b2030c', endColorstr='#6d0019',GradientType=0 );
                background-size: cover;
            }
            .header {
                width: 100%;
                display: table;
            }
            .userbox {
                background-color: #f8f8f8;
                position:absolute;
                top:0;
                right:0;
                width: 20vw;
                height: 8vh;
            }
            .gravatar {
                position: absolute;
                top:0;
                right:16vw;;
                width: 4vw;
                height: 8vh;
                background-color: black;
            }
            .userinfo {
                position:absolute;
                top:0;
                right:0;
                width: 16vw;
                height: 8vh;
                text-align: center;
                font-size: 0.75vw;
            }

            #inputs{
                width:100%;
                height: 100%;
            }
            .inputbox {
                padding-left: 5px;
                position: absolute;
                bottom: 5vh;
                left: 0;
                width: 35vw;
                height: 12vh;
                float: left;
            }
            #bottom-main {
                display: table-row;
            }
            .outputbox {
                width: 35vw;
                height: 12vh;
                position: absolute;
                bottom: 5vh;
                left: 37vw;
            }
            .iolabel {
                font-size: .85vw;
            }
            textarea {
                resize: none;
            }
            .io {
                display: table-cell;
                height: 11.5vh;
                width: 60vw;
            }
            .controls {
                display: table-cell;
                vertical-align: central;
                width: 20vw;
                height: 8vh;
            }
            .bigbuttons {
                font-family: 'Ubuntu', sans-serif;
                font-size: 11pt;
                border-color:#000;
                border-style:solid;
                border-radius:5px;
                margin-top:5px;
                margin-bottom:5px;
                width: 20%;
                height: 8vh;
            }
            .table {
                display: table;
            }
        </style>
        <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <form action="imaginarium.php" id="form" method="POST">
            <div class="userbox">
                <div class="gravatar">
                    <?php
                    require_once ($_SERVER['DOCUMENT_ROOT'] . '\data\user.php');
                    require_once ($_SERVER['DOCUMENT_ROOT'] . '\utility\front-utilities.php');
                    $user = User::get_user($_SESSION['uid']);
                    $affiliation = User::get_affiliation_name($_SESSION['uid']);
                    echo "<div class='avatar'><img style='width: 4vw;' src='" . get_gravatar($user['email']) . "' src='" . $user['fname'] . ' ' . $user['lname'] . "' /></div>";
                    ?>
                </div>
                <div class="userinfo">
                    <?php
                    echo 'Logged in As: ' . $user['fname'] . ' ' . $user['lname'] . ' <br>';
                    echo 'Affiliation:  ' . $affiliation . ' <br>';
                    echo '<a href="http://njit1.initiateid.com/">Click Here to Return to the Dashboard</a>';
                    ?>
                </div>
            </div>
            <div class="header" >
                <div class="top">
                    <div class="head">
                        <a href="index.php"><img src="images/logo.png" style="height: 8vh;" alt=""/></a>
                    </div>
                </div>
            </div>
            <div class="center">
                <div id="editor" class='editor'>/**
* NJIT High School Programming Contest
* Java - Code Imaginarium.
*/
public static void main(String[] args) {
    System.out.println("Hello World!");
}</div>
                <div id="hints">
                    <span id="position"></span>
                    <div id="dragbar"></div>
                    <h3>Welcome to the Code Imaginarium.</h3>
                    <p>You are using the Java Code Imaginarium</p>
                    <h3>Useful Java References</h3>
                    -<a href="http://java.com/en/">Java.com</a><br>
                    -<a href="https://docs.oracle.com/javase/8/docs/api/">Java 8 API Documentation</a><br>
                    -<a href="http://introcs.cs.princeton.edu/java/11cheatsheet/">Quick Java Cheat Sheet</a><br>
                </div>
            </div>
            <div class="bottom">
                <ul class="nav nav-tabs">
                    <li><a href="#">Files</a></li>
                    <li class="active"><a href="#">Console</a></li>
                    <li><a href="front.php">Question</a></li>
                </ul>  
                <div class='table'>
                    <div id='bottom-main'>
                        <div class="io">
                            <div class="inputbox">
                                <span class="iolabel">Inputs:</span> <br>
                                <textarea name='inputs' id="inputs"></textarea>
                            </div>
                            <div class="outputbox">
                                <span class="iolabel">Output:</span> <br>
                                <textarea name='outputs' id="outputs" style="width:100%; height: 100%" ><?php
                                    if (isset($outBox)) {
                                        echo $outBox;
                                    }
                                    ?></textarea>
                            </div>
                        </div>
                        <div class="controls">
                            <input type='hidden' name="code" id="code" value="">
                            <input type='hidden' name="language" value="java/output">
                            <input onclick="submitForm()" type="button" value="Execute" class="bigbuttons btn-default" >
                            <input onclick="clearEditor()" type="button" value="Clear" class="bigbuttons btn-default" >

                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script type="text/javascript">
            var i = 0;
            var dragging = false;
            $('#dragbar').mousedown(function (e) {
                e.preventDefault();

                dragging = true;
                var main = $('#main');
                var ghostbar = $('<div>',
                        {id: 'ghostbar',
                            css: {
                                height: main.outerHeight(),
                                top: main.offset().top,
                                left: main.offset().left
                            }
                        }).appendTo('body');

                $(document).mousemove(function (e) {
                    ghostbar.css("left", e.pageX + 2);
                });

            });

            $(document).mouseup(function (e) {
                if (dragging)
                {
                    var percentage = (e.pageX / window.innerWidth) * 100;
                    var mainPercentage = 100 - percentage;
                    $('#editor').css("width", percentage + "%");
                    $('#hints').css("width", mainPercentage + "%");
                    $('#ghostbar').remove();
                    $(document).unbind('mousemove');
                    dragging = false;
                }
            });
            var editor = ace.edit("editor");
            editor.setTheme("ace/theme/chrome");
            editor.getSession().setMode("ace/mode/java");
            editor.setFontSize(16);
            function clearEditor() {
                editor.setValue("", 0);
            }
            function submitForm() {
                document.getElementById('code').value = editor.getSession().getValue();
                //document.getElementById('inputs').value = editor.getSession().getValue();
                document.getElementById("form").submit();
            }
        </script>
    </body>
</html>
