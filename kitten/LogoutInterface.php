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

if(isset($_SESSION['user_email'])){
	$user_email = $_SESSION['user_email'];
} else {
	$user_email = "--undefined--";
}

?>
<style>
.logo{
	height:30px;
	width:30px;
}
</style>
<script>
</script>
<table id="logout_table">				
	<tr>	
		<td>
			<a href="" id="authlink" class="button" style="display:none">Authenticate Dropbox</a>
			<span id="dropbox_account_email" class="account_info text-white">No dropbox account linked yet</span>
		</td>
		
		<td>			
			<img class='logo' style="border-radius: 50%; background-color:white; padding:2px" src="https://www.open-collector.org/logos/dbx.ico.png" id="dropbox_logo">
		</td>
    <td>
      <span id="save_status" class="text-white"></span>
    </td>		
		<td>
			<span id="collector_account_email" class="account_info text-white"></span>
		</td>
		
		<td>
			<img class='logo' style="border-radius: 50%; background-color:white; padding:2px" src="https://www.open-collector.org/logos/collector.ico.png" id="collector_logo">
		</td>
    <td>
      <form action="login.php" method="post" style="padding:0px">	
        <button id="logout_btn" type="submit" name="login_type" value="logout" class="btn btn-primary">Log out</button>        
      </form>
    </td>
	</tr>
</table>
<script>

user_data = {
	email : '<?= $user_email ?>'	
}
		
$("#collector_account_email").html(user_data.email);


function highlight_account(element_id){
  $("#"+element_id).animate(
    {
      fontSize:"20px"
    },
    {
      duration:1000,
      complete:function(){
        $("#"+element_id).animate(
          {
            fontSize:"0px"
          },
          {
            duration:1000            
          }
        );
      }
    }
  );
}

$(".logo").hover(function(){
  var account_id = this.id.replace("logo","account_email");
  $("#"+account_id).show(500);
	$("#"+account_id).animate({
    fontSize:"14px"
  },500);
},function(){
  var account_id = this.id.replace("logo","account_email");  
  $("#"+account_id).animate({
    fontSize:"0px"
  },500);  
});

$("#collector_logo").on("click", function(){
  bootbox.confirm("Are you sure you want to log-out?",function(response){
    if(response){      
      $("#logout_btn").click();
    }
  });  
});

var local_website = "<?= $_SESSION['local_website'] ?>";

$("#dropbox_logo").on("click", function(){
	var dialog = bootbox.dialog({
	title: 'Dropbox account',
	message: "<p>Do you want to choose (another) dropbox account or view your dropbox files? <br><br> <b>NOTE</b>: Before logging out, make sure you have saved everything you want to.</p>",
	buttons: {
		cancel: {
			label: "View",
			className: 'btn-primary',
			callback: function(){
				window.open('https://www.dropbox.com/home/Apps/Open-Collector','_blank');
			}
		},
		noclose: {
			label: "Select Account",
			className: 'btn-info',
			callback: function(){
				dbx.setClientId(CLIENT_ID); // i think is necessary				
				if(local_website.indexOf("localhost") !== -1){
					local_website += "/www";
				}
				authUrl = dbx.getAuthenticationUrl(local_website+'/<?= $_SESSION['version'] ?>');
				authUrl += "&force_reauthentication=true";	
				document.getElementById('authlink').href = authUrl;
				$("#authlink")[0].click();
			}
		},
		ok: {
			label: "Cancel",
			className: 'btn-secondary',							
		}
	}
	});
});
</script>