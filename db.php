<?php 
	//Unicenta Localhost Connection
	$link = mysqli_connect("localhost","root","","unicenta") or die("Error Making Connection POS" . mysqli_error($link));
	
	//Magento Server Connection
	$link2 = mysqli_connect("1.1.1.1","user","password","dbname") or die("Error Making Connection Magento" . mysqli_error($link2));
   ?>