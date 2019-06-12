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
$default_surveys 				 = glob('IndexTabs/Surveys/default/*.csv');
$default_surveys_content = new stdClass();

foreach($default_surveys as $default_survey){
  $default_survey_name = str_replace("IndexTabs/Surveys/default/","",$default_survey);  
	$default_surveys_content ->  $default_survey_name = file_get_contents($default_survey);
}

?>
<style>
#spreadsheet_preview_tabs{
	display:none;
}
#survey_cell_view{	
	width:100%
}
#survey_interface{
	padding: 5px;
}
</style>

<table>
  <tr>
    <td>
      <button class='btn btn-primary' type='button' id="new_survey_button" title="Will create a new survey based on the one you have selected. Please select one of the existing surveys if you haven't already to be able to create a new survey based on it.">New Survey</button>
    </td>
    <td>
      <select id='survey_select' class="form-control">
        <option disabled selected>Please select a survey</option>
      </select>
    </td>
    <td>
      <button id='save_survey_btn' class='btn btn-primary'>Save</button>
			<button id='delete_survey_btn' class='btn btn-primary'>Delete</button>
    </td>
    <td id="spreadsheet_preview_tabs">
      <ul  class="nav nav-pills mb-3 bg-secondary" id="pills-tab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active text-white" id="pills-spreadsheet-tab" data-toggle="pill" href="#pills-spreadsheet" role="tab" aria-controls="pills-spreadsheet" aria-selected="true">Spreadsheet</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" id="pills-preview-tab" data-toggle="pill" href="#pills-preview" role="tab" aria-controls="pills-preview" aria-selected="false">Preview</a>
        </li>	
      </ul>		
    </td>    
  </tr>
</table>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-spreadsheet" role="tabpanel" aria-labelledby="pills-spreadsheet-tab">
    <div id="survey_HoT"></div>						
  </div>
  <div class="tab-pane fade" id="pills-preview" role="tabpanel" aria-labelledby="pills-preview-tab">
    <div id="survey_preview"></div>						
  </div>						
</div>	


<script>
///////////////
// functions //
///////////////

default_surveys = <?= json_encode($default_surveys_content) ?>;
Object.keys(default_surveys).forEach(function(survey){
	default_surveys[survey] = Papa.parse(default_surveys[survey]).data;
});

var survey_HoT;

function list_surveys(){
	if(typeof(megaUberJson.surveys) == "undefined"){
		megaUberJson.surveys = {
			preview         : false,
			user_surveys    : {}
		}
  }
	megaUberJson.surveys = typeof(megaUberJson.surveys) == "undefined" ? {} : megaUberJson.surveys;
	megaUberJson.surveys.default_surveys = default_surveys;

	megaUberJson.surveys.user_surveys = typeof(megaUberJson.surveys.user_surveys) == "undefined" ? {} : megaUberJson.surveys.user_surveys;
	megaUberJson.surveys.default_surveys = clean_obj_keys(megaUberJson.surveys.default_surveys);
	//megaUberJson.surveys.user_surveys = clean_obj_keys(megaUberJson.surveys.user_surveys);
	
	var def_survey_list  = Object.keys(megaUberJson.surveys.default_surveys).sort();
	var user_survey_list = Object.keys(megaUberJson.surveys.user_surveys).sort();
	
  def_survey_list.forEach(function(survey){    
    $("#survey_select").append($("<option>",{
			text:survey,
			value:"default|" + survey,
			class:"bg-info text-white"
		}));
	});
	user_survey_list.forEach(function(user_survey){    
    $("#survey_select").append($("<option>",{
			text:user_survey,
			value:"user|" + user_survey,
			class:"bg-white text-dark"
		}));
	});
	synch_surveys();
}

function synch_surveys(){
	dbx.filesListFolder({path:"/surveys"})
		.then(function(result){
			result.entries.forEach(function(entry){
				if(typeof(megaUberJson.surveys.user_surveys[entry.name.toLowerCase()]) == "undefined"){
					dbx.sharingCreateSharedLink({path:entry.path_lower})
						.then(function(shared_link){
							$.get(shared_link.url.replace("www.","dl."),function(result){
								var survey_name = entry.name.toLowerCase().replace(".csv","");
								megaUberJson.surveys.user_surveys[survey_name] = Papa.parse(result).data;								
							});							
						})
						.catch(function(error){
							report_error(error);							
						});					
				}				
			});
		})
		.catch(function(error){
			report_error(error);
		});
};

function create_survey_HoT(this_survey){
	var container = document.getElementById('survey_HoT');
	$("#survey_HoT").html("");
  survey_HoT = new Handsontable(container, {
		data: this_survey,
		minSpareCols: 1,
		minSpareRows: 1,
		rowHeaders: false,
		colHeaders: false,
		contextMenu: true,
		colWidths:100,
		rowHeights: 1,
		wordWrap: false,
		observeChanges: true,
		afterSelectionEnd: function(){
			thisCellValue = this.getValue();	
			$("#survey_cell_view").val(thisCellValue);
			$("#survey_cell_view").show();
			
			//clearTimeout(disable_cell_timeout);
			var coords        = this.getSelected();
			var column        = this.getDataAtCell(0,coords[1]); 
			var thisCellValue = this.getDataAtCell(coords[0],coords[1]);
			thisCellValue = thisCellValue == null ? thisCellValue = "" : thisCellValue;
			column        = column        == null ? column        = "" : column;
			
			helperActivate(column, thisCellValue,"survey");			
		},		
		afterDeselect: function(){
			$("#survey_cell_view").hide();
		},
		afterChange: function(){
      if(typeof(survey_HoT) !== "undefined"){
				update_user_survey();
			}
			var middleColEmpty=0;
			var middleRowEmpty=0;
			var postEmptyCol=0; //identify if there is a used col after empty one
			var postEmptyRow=0; // same for rows
			
			for (var k=0; k<this.countCols()-1; k++){
					//checking for invalid item number (1)
					
					//Removing Empty middle columns
					if (this.isEmptyCol(k)){
							if (middleColEmpty==0){
									middleColEmpty=1;
							}
					}            
					if (!this.isEmptyCol(k) & middleColEmpty==1){
							postEmptyCol =1;
							this.alter("remove_col",k-1); //delete column that is empty 
							middleColEmpty=0;
					}            
			}
			
			//Same thing for rows
			for (var k=0; k<this.countRows()-1; k++){
				if (this.isEmptyRow(k)){
					if (middleRowEmpty==0){
						middleRowEmpty=1;
					}
				}            
				if (!this.isEmptyRow(k) & middleRowEmpty==1){
					postEmptyRow =1;
					this.alter("remove_row",k-1); //delete column that is empty
					middleRowEmpty=0;
				}            
			}        
			if(postEmptyCol != 1 ){
				while(this.countEmptyCols()>1){  
					this.alter("remove_col",this.countCols); //delete the last col
				}
			}
			if(postEmptyRow != 1){
				while(this.countEmptyRows()>1){  
					this.alter("remove_row",this.countRows);//delete the last row
				}
			}   
            
			var this_survey_name = $("#survey_select").val();
			var this_survey = this.getData().filter(function(row){ return row.join("") !== '' });
		}
	});	
	
	var this_survey_name = $("#survey_select").val();	
	preview_survey(this_survey);		
}

function update_exp_survey(survey_name,survey_content){
	if(typeof(exp_json.surveys) == "undefined"){
		exp_json.surveys = {};
	}
	exp_json.surveys[survey_name] = survey_content;
}

function update_user_survey(){
  var survey_name    = $("#survey_select").val().split("|")[1];
  var survey_content = survey_HoT.getData();
  megaUberJson.surveys.user_surveys[survey_name] = survey_content;
  
  //update the currently loaded experiment?
  if(exp_json !== ""){							//check it has this survey in any of the proc sheets    
    parse_sheets("procedure");  		// update exp_json.parsed_procs
		Object.keys(exp_json.parsed_procs).forEach(function(this_proc){
			var this_proc_sheet = exp_json.parsed_procs[this_proc];
			this_proc_sheet.forEach(function(row){
				if(typeof(row["survey"]) !== "undefined" && row["survey"] == survey_name){
					update_exp_survey(row["survey"].toLowerCase(),survey_content);
				}
			});
		});	    
  }
}


function preview_survey(preview_survey){
	
  megaUberJson.surveys.preview = true;
  if(typeof(megaUberJson.trialtypes.user_trialtypes.surveycat) !== "undefined"){
    survey_template = megaUberJson.trialtypes.user_trialtypes.surveycat;
  } else {
    survey_template = megaUberJson.trialtypes.default_trialtypes.surveycat;
  }

	//NOTE that this needs to change to "default" once the trialtype is finally finalised
	survey_template = survey_template.replace('load_survey("{{survey}}");','load_survey('+JSON.stringify(preview_survey)+')');
	$("#survey_preview").html(survey_template);		
}


	
//////////////////////
// element triggers //
//////////////////////

$("#delete_survey_btn").on("click",function(){
	bootbox.confirm("Are you sure you want to delete this survey?",function(confirmed){
		if(confirmed){
			var survey_name = $("#survey_select").val().split("|")[1].toLowerCase().replace(".csv","") + ".csv";
			delete megaUberJson.surveys.user_surveys[survey_name];
			dbx.filesDelete({path:"/surveys/" + survey_name})
				.then(function(result){
					console.dir(result);
					custom_alert("Succesfully deleted <b>" + survey_name + "</b>");
					$('#survey_select option[value="' + $("#survey_select").val() + '"]').remove();
					$("#survey_select").val("default|demographics.csv");
					create_survey_HoT(megaUberJson.surveys.default_surveys["demographics.csv"]);
					$("#save_btn").click();
				})
				.catch(function(error){
					report_error(error);
				});
		}
	});
});

$("#new_survey_button").on("click",function(){
	if($("#survey_select").val() == null){
		bootbox.alert("Please select a survey that already exists to base the new survey on. To do this, click on the dropdown list that has 'Please select a survey' written in it.");
	} else {
		bootbox.confirm("The new survey will be based on the one that you've selected, are you sure you want to confirm", function(result){
			if(result){
				bootbox.prompt({
					title: "New Survey",
					callback: function(survey_name){
						survey_name = survey_name.toLowerCase().replaceAll(".csv","");
						if(typeof(megaUberJson.surveys.user_surveys[survey_name] == "undefined")){
							var survey_content = survey_HoT.getData();
							megaUberJson.surveys.user_surveys[survey_name] = survey_content;
							var survey_value   = "user|" + survey_name;
							$("#survey_select").append($("<option>",{
								text  : survey_name,
								value : survey_value,
								class : "bg-info text-white"
							}));
							$("#survey_select").val(survey_value);
							custom_alert("<b>"+survey_name+"</b> created succesfully");
						} else {
							bootbox.alert("Survey name already exists");
						}			
					}
				});
			}
		});
	}
});

$("#save_survey_btn").on("click",function(){
	if($("#survey_select").val() !== null){
		var survey_name     = $("#survey_select").val().split("|");
		var survey_content  = Papa.unparse(survey_HoT.getData());
		dbx_obj.new_upload({
			path    : "/surveys/" + survey_name[1].replace(".csv","") + ".csv",
			contents : survey_content,
			mode    : "overwrite"
		},function(){
			custom_alert("Success!");
			updateUberMegaFile();
		},function(error){
			custom_alert("error - check console!");
			console.dir(error);
		});		
		
	}
});

$("#pills-preview-tab").on("click",function(){
  var this_survey =  survey_HoT.getData();
  preview_survey(this_survey);
});

$("#survey_select").on("change",function(){
  
  var this_survey = $("#survey_select").val().split("|");
  
  if(this_survey[0] == "default"){
    $("#survey_select").removeClass("bg-light");
    $("#survey_select").addClass("bg-info");
    $("#survey_select").addClass("text-white");
    
    create_survey_HoT(megaUberJson.surveys.default_surveys[this_survey[1]]);	
    $("#spreadsheet_preview_tabs").show();
    
    
  } else if(this_survey[0] == "user"){
    $("#survey_select").removeClass("bg-info");
    $("#survey_select").removeClass("text-white");
    $("#survey_select").addClass("bg-light");
		
		create_survey_HoT(megaUberJson.surveys.user_surveys[this_survey[1]]);	
    $("#spreadsheet_preview_tabs").show();
  } else {
    bootbox.alert("It's not clear whether this is supposed to be a default or user_trialtype");
  }

});

</script>