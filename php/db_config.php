<?php
$servername='localhost';$username='root';$password='';$dbname='gestio_explotacio';
$conn=new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){die('Error de connexió: '.$conn->connect_error);}?>