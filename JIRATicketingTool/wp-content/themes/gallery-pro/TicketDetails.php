<?php /* Template Name: TicketDetail */ ?>
<?php 
get_header(); 
?>
<script>  
var IssueTypeID = localStorage.getItem("IssueTypeID");
var TcktDtl = localStorage.getItem("refid");
var ProjKey = localStorage.getItem("ProjectKey");
var fieldUrl = "https://digitalrequest.intel.com/rest/api/2/issue/createmeta?projectKeys="+ProjKey+"&issuetypeIds="+IssueTypeID+"&expand=projects.issuetypes.fields"

  var ticketDetails = {};
  var fieldData = {};
  jQuery(document).ready(function () {
  	jQuery.ajax({
  		type: "GET",
  		url: fieldUrl,
  		dataType: 'json',
  		async: false,
  		headers: {
  			"Authorization": "Basic " + btoa("username:password")
  		},
  		success: function (responseData) {
  			FieldsTitleData(responseData);
  			jQuery.ajax({
  				type: "GET",
  				url: TcktDtl,
  				dataType: 'json',
  				async: false,
  				headers: {
  					"Authorization": "Basic " + btoa("username:password")
  					},
  					success: function (TicketResponseData) {
  						TicketDetails(TicketResponseData);
  					}
  				});
  			}
  		});
  	});
  fieldData.jsonObj = [];

  function FieldsTitleData(responseData) {
  for (var j = 0; j < responseData.projects.length; j++) {
                var cnt = responseData.projects[j].issuetypes.length;
                for (var i = 0; i < cnt; i++) {
                    //fields
                    fieldData.jsonObj.Fields = [];
                    var fieldCnt = Object.keys(responseData.projects[j].issuetypes[i].fields).length;
                    for (var k = 0; k < fieldCnt; k++) {

                        var strKey = Object.keys(responseData.projects[j].issuetypes[i].fields)[k];
                        var strName = responseData.projects[j].issuetypes[i].fields[strKey].name;

                        var fieldType = responseData.projects[j].issuetypes[i].fields[strKey].schema.custom;
                        if (fieldType != undefined) {
                            fieldType = fieldType.split(":");
                            fieldType = fieldType[1];
                        }
                        else
                            fieldType = responseData.projects[j].issuetypes[i].fields[strKey].schema.type;

                        fieldData.jsonObj.Fields.push({
                            "Key": strKey,
                            "fieldType": fieldType,
                            "Title": strName
                        });
                    }
                }

                fieldData.jsonObj.push({
                    'fieldTitles': fieldData.jsonObj.Fields
                });
            }

             var abcs = JSON.stringify(fieldData.jsonObj);

        }

        function TicketDetails(TicketResponseData) {
            var table = document.getElementById("TicketDetails");
            var abcs = JSON.stringify(TicketResponseData);
            var tblBody = document.createElement('tbody');
            table.appendChild(tblBody);

            var fieldCnt = Object.keys(TicketResponseData.fields).length;

            ticketDetails.jsonObj = [];
            for (var i = 0; i < fieldCnt; i++) {
                var strKey = Object.keys(TicketResponseData.fields)[i];

                for (var j = 0; j < fieldData.jsonObj[0].fieldTitles.length; j++) {
                    if (fieldData.jsonObj[0].fieldTitles[j].Key == strKey) {
                        var tr = document.createElement('tr');
                        tblBody.appendChild(tr);
                        var tdname1 = document.createElement('td');

                        tdname1.appendChild(document.createTextNode(fieldData.jsonObj[0].fieldTitles[j].Title));
                        tdname1.style.fontWeight = 'bold';
                        tdname1.width = "25%";
                        tr.appendChild(tdname1);
                        var tdname2 = document.createElement('td');
                        var fldtype = fieldData.jsonObj[0].fieldTitles[j].fieldType;
                        if (fldtype == "select") {
                            tdname2.appendChild(document.createTextNode(TicketResponseData.fields[strKey].value));
                        }
                        if (fldtype == "multiselect") {                            
                            var strListValue = "";
                            if (!(TicketResponseData.fields[strKey] == null)) {                            
                            var valueCnt = TicketResponseData.fields[strKey].length;
                            for (var l = 0; l < valueCnt; l++) {
                                if (l == (valueCnt - 1))
                                    strListValue = strListValue + TicketResponseData.fields[strKey][l].value;
                                else
                                    strListValue = strListValue + TicketResponseData.fields[strKey][l].value + ", ";
                            }
                            tdname2.appendChild(document.createTextNode(strListValue));
                            }
                            else
                            tdname2.appendChild(document.createTextNode("None"));
                        }
                        if (fldtype == "textarea" || fldtype == "textfield" || fldtype == "string" || fldtype == "datepicker"|| fldtype == "datetime") {
                            tdname2.appendChild(document.createTextNode(TicketResponseData.fields[strKey]));
                        }
                        if (fldtype == "radiobuttons") {
                            if(!(TicketResponseData.fields[strKey] == null))
                                tdname2.appendChild(document.createTextNode(TicketResponseData.fields[strKey].value));
                            else
                                tdname2.appendChild(document.createTextNode("None"));
                        }
                        if (fldtype == "issuetype" || fldtype == "project") {
                            tdname2.appendChild(document.createTextNode(TicketResponseData.fields[strKey].name));
                        }
						if (fldtype == "array") {
                            var attachmentCnt = TicketResponseData.fields[strKey].length;
                            for (var m = 0; m < attachmentCnt; m++) {
                                var anchor = document.createElement('a');
                                anchor.href = TicketResponseData.fields[strKey][m].content;
                                anchor.innerText = TicketResponseData.fields[strKey][m].filename;
								anchor.target="_blank";
                                tdname2.appendChild(anchor);
                                lineBreak = document.createElement("br");
                                tdname2.appendChild(lineBreak);
                            }
                        }
                        tr.appendChild(tdname2);
                    }
                }
            }
        }		
          
		function Dashboard()
		{
			document.location.href='dashboard';
		}
</script>
<div><span style="font-size:25px;">Ticket Details</span><input id="btnBack" type="Submit" value="Back" onclick="Dashboard()" style="float:right" class="btn btn-primary"/></div></br>
<div style="overflow-y:scroll; max-height:500px;">
<table id="TicketDetails" class="table table-striped"></table>
</div>

<?php get_footer(); ?>
