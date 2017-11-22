<?php /* Template Name: Dashboard */ ?>
<?php 
get_header(); 
$winuser = getenv("username"); 
 ?>
<script>  
var Loggedin= "<?php echo $winuser;?>";
var userName = Loggedin.split('-');
var uName = userName[0].toLowerCase();
var JiraUrl = "https://digitalrequest.intel.com/rest/api/2/search?jql=project=RMM%20AND%20Reporter="+uName;
var refID="";
var ticketLists = {};
    jQuery(document).ready(function(){
		 jQuery.ajax({
                type: "GET",
                url: JiraUrl,
                dataType: 'json',
                async: false,
                headers: {
                    "Authorization": "Basic " + btoa("username:password")
                },
                success: function (jsonData) {
                    //alert('API Connection Successful!');                                        
                    FormatData(jsonData);                  
                }
            });			
    });
	 function FormatData(responseData) {      
          
            ticketLists.TicketList = [];
            var cnt = responseData.issues.length;
            for (var i = 0; i < cnt; i++) {
                ticketLists.TicketList.push({
                    "Project": responseData.issues[i].fields.project.name, 
					"ProjectKey": responseData.issues[i].fields.project.key, 
					"IssueKey":  "<a href='https://wrp-platform.iglb.intel.com/digihr/ticket-details/'>" + responseData.issues[i].key + "</a>", 
					"IssueIDRef":responseData.issues[i].self, 
					"IssueTypeID":responseData.issues[i].fields.issuetype.id, 
					"IssueType": responseData.issues[i].fields.issuetype.name, 
					"TicketSummary": responseData.issues[i].fields.summary, 
					"Priority": responseData.issues[i].fields.priority.name, 
					"Status": responseData.issues[i].fields.status.name, 
					"CreatedDate": responseData.issues[i].fields.created.substring(0, 10), 
					"ClosedDate": responseData.issues[i].fields.customfield_11622 == null? null: responseData.issues[i].fields.customfield_11622.substring(0, 10)
                });
            }	
        
		
		jQuery("#jsGrid").jsGrid({
        width: "100%",
        height: "500px",
        sorting: true,
        paging: true, 
        data: ticketLists.TicketList, 
        fields: [
            { name: "IssueKey", title:"Request ID",type: "text", width: 30 },
			{ name: "IssueIDRef", title:"Issue ID Ref",type: "text", width: 30, css: "hide" },
			/*{ name: "Project", title: "Project Name", type: "text", width: 50 }, */
            { name: "IssueType", title:"Issue Type",type: "text" },
			{ name: "TicketSummary", title:"Ticket Summary",type: "text", width: 150 },
			{ name: "Priority", title:"Priority",type: "text", width: 25 },
			{ name: "Status", title:"Status",type: "text", width: 50 },
			{ name: "CreatedDate", title:"Created Date",type: "date", width: 50 },{ name: "ClosedDate", title:"Closed Date",type: "text", width: 50 }
           ],
		   rowClick: function(args) {    
    var getData = args.item;
	refID = getData.IssueIDRef;	 
	localStorage.setItem("refid",refID); 
	IssueTypeID = getData.IssueTypeID;	 
	localStorage.setItem("IssueTypeID",IssueTypeID); 
	ProjectKey = getData.ProjectKey;	 
	localStorage.setItem("ProjectKey",ProjectKey);	
  }
    });
	 }
	 
	 function CreateTicket()
	 {
		 document.location.href='create-request';
	 }
</script>
<div><span style="font-size:25px;">Relationship Marketing Request Tool<span><input id="Button1" type="Submit" value="Create Request" onclick="CreateTicket()" style="float:right" class="btn btn-primary"/></div>
</br>
<div id="jsGrid" ></div>
