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

require_once 'Code/initiateCollector.php';

$url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

?>
<style>
#welcome_div{
	padding:	10px; 
	width: 		500px; 
	height:		500px; 
	position: absolute;
  top:			0;
  bottom: 	0;
  left: 		0;
  right: 		0;
  margin: 	auto;
}
</style>

<div id="welcome_div">
	<div id="loading_exp_json">Loading ... please wait</div>
	<div id="researcher_message" style="display:none"></div>
	
	<br><br>
	<div id="warning_pp_code" class="text-danger" style="display:none"><b>Illegal character entered, replaced with legal alternative</b></div>
	<div class="input-group mb-3" style='width:500px; display:none' id="participant_id_div">		
		<input id="participant_code" type="text" class="form-control" placeholder="Please type the ID the researcher needs here" aria-label="participant_code">
		<div class="input-group-append">
			<button class="btn btn-primary" type="button" id="start_btn">Start</button>
		</div>
	</div>
	<input type='hidden' name='return_page' value='<?= $url ?>' />
</div>

<script>

$("#participant_code").on("input change",function(){
	var original_pp_code = $("#participant_code").val();
	var this_pp_code = $("#participant_code").val();
	this_pp_code = this_pp_code.replaceAll(" ","_");
	this_pp_code = this_pp_code.replaceAll("-","_");
	this_pp_code = this_pp_code.replaceAll("@","_at_");
	this_pp_code = this_pp_code.replaceAll(".","_dot_");
	this_pp_code = this_pp_code.replaceAll("/","_forward_slash_");
	this_pp_code = this_pp_code.replaceAll("\\","_back_slash");
	this_pp_code = this_pp_code.replaceAll("'","_single_quote_");
	this_pp_code = this_pp_code.replaceAll('"',"_double_quote_");
	this_pp_code = this_pp_code.replaceAll('|',"_pipe_");
	this_pp_code = this_pp_code.replaceAll('?',"_question_");
	this_pp_code = this_pp_code.replaceAll('#',"_hash_");
	this_pp_code = this_pp_code.replaceAll(',',"_comma_");
	this_pp_code = this_pp_code.replaceAll('[',"_square_open_");
	this_pp_code = this_pp_code.replaceAll(']',"_square_close_");
	this_pp_code = this_pp_code.replaceAll('(',"_bracket_open_");
	this_pp_code = this_pp_code.replaceAll(')',"_bracket_close_");
	this_pp_code = this_pp_code.replaceAll('*',"__");
	this_pp_code = this_pp_code.replaceAll('^',"__");
	this_pp_code = this_pp_code.replaceAll('%',"__");
	this_pp_code = this_pp_code.replaceAll('$',"__");
	this_pp_code = this_pp_code.replaceAll('£',"__");
	this_pp_code = this_pp_code.replaceAll('!',"__");
	this_pp_code = this_pp_code.replaceAll('`',"__");
	this_pp_code = this_pp_code.replaceAll('`',"__");
	this_pp_code = this_pp_code.replaceAll('`',"__");
	this_pp_code = this_pp_code.replaceAll('`',"__");
	this_pp_code = this_pp_code.replaceAll('+',"__");
	this_pp_code = this_pp_code.replaceAll('=',"__");
	this_pp_code = this_pp_code.replaceAll('<',"__");
	this_pp_code = this_pp_code.replaceAll('>',"__");
	this_pp_code = this_pp_code.replaceAll('~',"__");
	this_pp_code = this_pp_code.toLowerCase();
	if(original_pp_code !== this_pp_code){
		$("#warning_pp_code").fadeIn(500);
		setTimeout(function(){
			$("#warning_pp_code").fadeOut(500);
		},3000);
	}
	$("#participant_code").val(this_pp_code);
});

$("#start_btn").on("click",function(){
	var participant_code = $("#participant_code").val();
	bootbox.prompt("Please confirm your ID in the text box below", function(confirmed_id){
		if(confirmed_id == participant_code){
			post_welcome(participant_code,false);	
		} else {
			bootbox.alert("The IDs you've typed in do not match. Please double check and try again");
		}
	});  
});

function post_welcome(participant_code,id_error){
	$.post("PostWelcome.php",{
		participant_code : participant_code,
		location: exp_json.location
	},function(returned_data){
		console.dir(returned_data);
		if(returned_data.indexOf("error") !== -1){
			if(id_error == "skip"){ 
				$("#welcome_div").hide();
				$("#post_welcome").show();
				$("#experiment_div").show();
        full_screen();
			} else if(id_error == "random"){
				
				//generate new random code
				var this_code = Math.random().toString(36).substr(2, 16)
				post_welcome(this_code,"random");
				
			} else if(id_error == false){ 
				bootbox.confirm(returned_data,function(response){
					if(response){ 																		//i.e. chosen to proceed with id despite the warning
						$("#welcome_div").hide();
						$("#post_welcome").show();
						$("#experiment_div").show();
            full_screen();
					}
				});
			}
		} else {
			$("#welcome_div").hide();
			$("#post_welcome").show();
			$("#experiment_div").show();					
		}
	});
}



</script>