<?php /* Template Name: CreateTicket */ ?>
<?php 
get_header(); 
?>
<script>  

var ticketDetails = {};
        var fieldData = {};		
		
    jQuery(document).ready(function(){
		  jQuery.ajax({
                type: "GET",                
                url: "https://digitalrequest.intel.com/rest/api/2/issue/createmeta?projectKeys=RMM&expand=projects.issuetypes.fields",                
                dataType: 'json',
                async: false,
                headers: {
                    "Authorization": "Basic " + btoa("username:password")
                },
                success: function (jsonData) {
                    FormatData(jsonData);
                }
            });	
			
			 var element = document.getElementById('issuetype');
				element.onchange = function (value) {
                var counter = document.getElementById('issuetype').selectedIndex;                
                var rows = document.getElementById("CreateIssue").getElementsByTagName("tr").length;
                for (var i = 2; i < rows - 1; i++) {
                    document.getElementById("CreateIssue").deleteRow(2);
                }
                CreateForm(counter);
            }
    });
	
		function SubmitTicket() {
			var attachmentFlg = false;
            fieldData.fields = {};

            var counter = document.getElementById('issuetype').selectedIndex;
            fields = {}
            for (var i = 0; i < ticketDetails.jsonObj[0].IssueTypes[counter].Fields.length; i++) {
                var fldType = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].fieldType;
                if (fldType == "textarea" || fldType == "textfield" || fldType == "string" || fldType == "datepicker" || fldType == "datetime") {
                    var key = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].key;
                    var value = document.getElementById(key).value;
                    if (value != "") {
                        fields[key] = value;
                    }
                }
                else if (fldType == "select") {
                    fieldArray = {}
                    var key = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].key;
                    var e = document.getElementById(key);
                    var value = e.options[e.selectedIndex].text;
                    fieldArray["value"] = value;
                    fields[key] = fieldArray;

                }
                else if (fldType == "radiobuttons") {
                    fieldArray = {}
                    var key = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].key;
                    var radios = document.getElementsByName(key);
                    for (var k = 0; k < radios.length; k++) {
                        if (radios[k].checked) {
                            if (!(radios[k].value == "-1")) {
                                fieldArray["value"] = radios[k].value;
                                fields[key] = fieldArray;
                            }
                            break;
                        }
                    }
                }
                else if (fldType == "multiselect") {
                    multiSelect = [];
                    var key = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].key;
                    var x = document.getElementById(key);
                    for (var j = 0; j < x.options.length; j++) {
                        if (x.options[j].selected) {
                            if (!(x.options[j].text == "None")) {
                                multiSelect.push({ "value": x.options[j].text })
                            }
                        }
                    }
                    fields[key] = multiSelect;

                }
                else if (fldType == "issuetype" || fldType == "project") {
                    fieldArray = {}
                    var key = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[i].key;
                    var e = document.getElementById(key);
                    var value = e.options[e.selectedIndex].value;
                    fieldArray["id"] = value;
                    fields[key] = fieldArray;
                }
				else if (fldType == "array") {                    
                    var attachmentCnt = document.getElementById('fileUpload').files.length;
                    if (attachmentCnt>0) {
                        attachmentFlg = true;
                    }                    
                }
            }
            fieldData.fields["fields"] = fields;

            var myJSON = JSON.stringify(fieldData.fields);
            jQuery.ajax({
                type: "POST",
                url: "https://digitalrequest.intel.com/rest/api/2/issue",
                data: myJSON,
                contentType: "application/json",
                async: false,
                dataType: 'json',
                headers: {
                    "Authorization": "Basic " + btoa("username:password"),
                    'X-Atlassian-Token': 'no-check',
                },
                success: function (repsonseData) {
                    alert(repsonseData.key + " is Successfully created.");				
						
                    if (attachmentFlg){
                        addAtatchment(repsonseData);
					}
                    Dashboard();
			
                },
                error: function (repsonseData) {
                    alert("failed");
                }
            });
        }

        ticketDetails.jsonObj = [];
		var table = "";
		var tblBody = "";
        function FormatData(responseData) {

            for (var j = 0; j < responseData.projects.length; j++) {
                ticketDetails.jsonObj.IssueTypes = [];
                var cnt = responseData.projects[j].issuetypes.length;
                for (var i = 0; i < cnt; i++) {
                    //fields
                    ticketDetails.jsonObj.Fields = [];
                    var fieldCnt = Object.keys(responseData.projects[j].issuetypes[i].fields).length;
                    for (var k = 0; k < fieldCnt; k++) {

                        var strKey = Object.keys(responseData.projects[j].issuetypes[i].fields)[k];
                        var strName = responseData.projects[j].issuetypes[i].fields[strKey].name;
                        var strRequired = responseData.projects[j].issuetypes[i].fields[strKey].required;
                        var strallowedValues = responseData.projects[j].issuetypes[i].fields[strKey].allowedValues;
                        var fieldType = "text";
                        if (strallowedValues != undefined) {
                            ticketDetails.jsonObj.allowedValues = [];
                            var allowedValuesCnt = Object.keys(responseData.projects[j].issuetypes[i].fields[strKey].allowedValues).length;
                            for (var m = 0; m < allowedValuesCnt; m++) {
                                //var strAllowedKey = Object.keys(responseData.projects[j].issuetypes[i].fields[strKey].allowedValues)[m];
                                var strAllowedID = responseData.projects[j].issuetypes[i].fields[strKey].allowedValues[m].id;
                                var strAllowedName = responseData.projects[j].issuetypes[i].fields[strKey].allowedValues[m].name;
                                var strAllowedValue = responseData.projects[j].issuetypes[i].fields[strKey].allowedValues[m].value;
                                ticketDetails.jsonObj.allowedValues.push({
                                    "id": strAllowedID,
                                    "name": strAllowedName,
                                    "value": strAllowedValue
                                });
                                fieldType = "dropdown";
                            }
                        }
                        else
                            ticketDetails.jsonObj.allowedValues = null;

                        var fieldType = responseData.projects[j].issuetypes[i].fields[strKey].schema.custom;
                        if (fieldType != undefined) {
                            fieldType = fieldType.split(":");
                            fieldType = fieldType[1];
                        }
                        else
                            fieldType = responseData.projects[j].issuetypes[i].fields[strKey].schema.type;

                        ticketDetails.jsonObj.Fields.push({
                            "required": strRequired,
                            "key": strKey,
                            "name": strName,
                            "fieldType": fieldType,
                            "allowedValues": ticketDetails.jsonObj.allowedValues
                        });
                    }
                    //fields

                    ticketDetails.jsonObj.IssueTypes.push({
                        "ID": responseData.projects[j].issuetypes[i].id,
                        "ItemValue": responseData.projects[j].issuetypes[i].name,
                        "Fields": ticketDetails.jsonObj.Fields
                    });
                }

                ticketDetails.jsonObj.push({
                    'ProjectID': responseData.projects[j].id,
                    'ProjectKey': responseData.projects[j].key,
                    'ProjectName': responseData.projects[j].name,
                    'IssueTypes': ticketDetails.jsonObj.IssueTypes
                });
            }

            
            var ProjCnt = ticketDetails.jsonObj.length;
            table = document.getElementById("CreateIssue");
           
            tblBody = document.createElement('tbody');
            table.appendChild(tblBody);

            var tr = document.createElement('tr');
            tblBody.appendChild(tr);
            var tdname1 = document.createElement('td');
            tdname1.appendChild(document.createTextNode('Project'));
            tdname1.style.fontWeight = 'bold';
            tr.appendChild(tdname1);
            var tdname2 = document.createElement('td');
            //
            var projSelect = document.createElement('select');
            projSelect.id = "project";
            projSelect.className = "btn btn-default dropdown-toggle";
            for (var i = 0; i < ProjCnt; i++) {
                var option = document.createElement("option");
                option.text = ticketDetails.jsonObj[i].ProjectName;
                option.value = ticketDetails.jsonObj[i].ProjectID;
                projSelect.add(option);
            }
            tdname2.appendChild(projSelect);
            //
            tr.appendChild(tdname2);

            var tr1 = document.createElement('tr');
            tblBody.appendChild(tr1);
            var tdname11 = document.createElement('td');
            tdname11.appendChild(document.createTextNode('IssueType'));
            tdname11.style.fontWeight = 'bold';
            tr1.appendChild(tdname11);
            var tdname21 = document.createElement('td');
            var issueTypeCnt = ticketDetails.jsonObj[0].IssueTypes.length;
            var issueTypeSelect = document.createElement('select');
            issueTypeSelect.id = "issuetype";
            issueTypeSelect.className = "btn btn-default dropdown-toggle";
            for (var j = 0; j < issueTypeCnt; j++) {
                var option = document.createElement("option");
                option.text = ticketDetails.jsonObj[0].IssueTypes[j].ItemValue;
                option.value = ticketDetails.jsonObj[0].IssueTypes[j].ID
                issueTypeSelect.add(option);
            }
            tdname21.appendChild(issueTypeSelect);
            tr1.appendChild(tdname21);

            var counter = 0;            
            CreateForm(counter);

        }
		
        function CreateForm(counter) {
            var issueTypeCnt = ticketDetails.jsonObj[0].IssueTypes[counter].Fields.length;

            for (var f = 0; f < issueTypeCnt; f++) {
                var strLabel = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].name;
                var strFieldType = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].fieldType;
                var strFieldKey = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].key;
                var strFieldrequired = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].required;
                if (!(strLabel === "Issue Type" || strLabel === "Project")) {
                    var tr = document.createElement('tr');
                    tblBody.appendChild(tr);
                    var tdname1 = document.createElement('td');

                    
                    tdname1.appendChild(document.createTextNode(strLabel));
                    if (strFieldrequired) {
                        strspan = document.createElement("span");
                        strspan.style.color = "red";
                        strReq = "*";                        
                        strspan.appendChild(document.createTextNode(strReq));
                        tdname1.appendChild(strspan);
                       
                    }
                    tdname1.style.fontWeight = 'bold';
					tdname1.width = "25%";
                    tr.appendChild(tdname1);
                    var tdname2 = document.createElement('td');
                    //
                    var input = ""
                    if (strFieldType == "textfield") {
                        input = document.createElement("input");
                        input.id = strFieldKey;
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        } 
                        input.type = "text";
                        input.width = "100";

                    }
                    else if (strFieldType == "string") {
                        input = document.createElement("textarea");
                        input.id = strFieldKey;
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }
                        input.type = "text";
                        input.width = "100";

                    }
                  
                    else if (strFieldType == "radiobuttons") {
                        var input = document.createElement("div");
                        // None
                        var label = document.createElement("label");
                        radio = document.createElement("input");
                        radio.type = "radio";
                        radio.name = strFieldKey;
                        radio.value = "-1";
                        radio.checked = "checked";
                        label.appendChild(radio); 
                        var strNone = "None";
                        label.appendChild(document.createTextNode(strNone));
                        input.appendChild(label);
                        lineBreak = document.createElement("br");
                        input.appendChild(lineBreak);
                        //
                        for (var j = 0; j < ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues.length; j++) {
                            var label = document.createElement("label");
                            radio = document.createElement("input");
                            radio.type = "radio";
                            radio.name = strFieldKey;
                            radio.value = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].value;
                            label.appendChild(radio); 
                            label.appendChild(document.createTextNode(ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].value));
                            input.appendChild(label);
                            lineBreak = document.createElement("br");
                            input.appendChild(lineBreak);
                        }
                    }
                    // 
                    else if (strFieldType == "datepicker") {
                        input = document.createElement("input");
                        input.id = strFieldKey;
						input.className="date";
						input.placeholder = "yyyy-mm-dd";
                        input.maxlength = "10";
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }
                        input.type = "text";
						
                    }
					else if (strFieldType == "datetime") {
                        input = document.createElement("input");
                        input.id = strFieldKey;
						input.className="datetime";
						input.placeholder = "yyyy-mm-dd hh:mm:ss";
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }
                        input.type = "text";
						
                    }
                    else if (strFieldType == "textarea") {
                        input = document.createElement("textarea");
                        input.id = strFieldKey;
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }
                        input.cols = "80";
                        input.rows = "2"
                       
                    }
                    else if (strFieldType == "select") {
                        var input = document.createElement('select');
                        input.id = strFieldKey;
                        input.className = "btn btn-default dropdown-toggle";
                        onmousedown = "this.value='';";
                        input.onchange = "jsFunction(this.value);";
                        for (var j = 0; j < ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues.length; j++) {
                            var option = document.createElement("option");
                            option.text = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].value;
                            option.value = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].id;
                            input.add(option);
                        }
                        
                    }
                    else if (strFieldType == "multiselect") {
                        var input = document.createElement('select');
                        input.id = strFieldKey;
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }                        
                        var option = document.createElement("option");
                        option.text = "None";
                        option.value = -1;
                        option.selected = "selected";
                        input.add(option);
                        
                        input.multiple = true;
                        input.className = "btn btn-default dropdown-toggle";
                        for (var j = 0; j < ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues.length; j++) {
                            var option = document.createElement("option");
                            option.text = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].value;
                            option.value = ticketDetails.jsonObj[0].IssueTypes[counter].Fields[f].allowedValues[j].id;
                            input.add(option);
                        }
                        
                    }
                    else if (strFieldType == "array") {
                        input = document.createElement("input");
                        input.id = "fileUpload";
                        if (strFieldrequired) {
                            input.required = strFieldrequired;
                        }
                        input.type = "file";
                        input.multiple = "multiple";
                    }
                    tdname2.appendChild(input);
                    //
                    tr.appendChild(tdname2);
                }
            }
			 jQuery(".date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd"				
            });
			jQuery('.datetime').datetimepicker({
                controlType: 'select',
                oneLine: true,
                dateFormat: "yy-mm-dd",
                separator: 'T',
                timeFormat: 'hh:mm:ss.sz',
                ampm: false,
                timezoneIso8609: true                
            });
        }
		// Add Attachment
		function addAtatchment(repsonseData) {
            var formData = new FormData();
            for (var i = 0; i < document.getElementById('fileUpload').files.length; i++) {
                formData.append("file", jQuery('input[type=file]')[0].files[i], document.getElementById('fileUpload').files[i].name);
            }
            jQuery.ajax({
                type: "POST",
                url: repsonseData.self + "/attachments",
                data: formData,
                contentType: false,
                async: false,
                processData: false,
                headers: {
                    "Authorization": "Basic " + btoa("username:password"),
                    'X-Atlassian-Token': 'no-check',
                    'boundary': 'boundary'
                },
                success: function (repsonseData1) {
                    alert("Added Attachment Sucessfully.");
                },
                error: function (repsonseData1) {
                    alert("File(s) failled to attach to the issue");
                }
            });
        }
		
		function Dashboard()
		{
			document.location.href='dashboard';
		}
</script>
<div><span style="font-size:25px;">Create Request</span></div>
<div style="overflow-y:scroll; max-height:500px;">
<table id="CreateIssue" class="table table-striped"></table>
</div>
<br>
<div>
	<input id="btnCreate" type="Submit" value="Create" onclick="SubmitTicket()" style="float: right" class="btn btn-primary" />
    </div>

<?php get_footer(); ?>
