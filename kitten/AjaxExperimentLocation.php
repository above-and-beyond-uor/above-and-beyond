<?php
/*  Collector (Garcia, Kornell, Kerr, Blake & Haffey)
    A program for running experiments on the web
    Copyright 2012-2016 Mikey Garcia & Nate Kornell


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 
		Kitten release (2019) author: Dr. Anthony Haffey (a.haffey@reading.ac.uk)
*/


require 'Code/initiateCollector.php';
require_once "../../sqlConnect.php";

$location   = $_POST['location'];
$experiment = $_POST['experiment'];
$user_email = $_SESSION['user_email'];

$sql = "UPDATE `view_experiment_researchers` SET `location`='$location' WHERE `name`='$experiment' AND `email` = '$user_email'";
if ($conn->query($sql) === TRUE) {
	echo "success";				
} else {
	echo  $conn->error;;
}
?>