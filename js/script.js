// Strict Mode
"use strict";

var timer;
var filterClicked = 'no';
var chartdates;
var chartdelivered;
var chartcanceled;

// Window Load Event
$(window).on("load", function() {

    return false;
});

// Document Ready event
$(document).on("ready", function() {
	// Google Sheet imports belong only to the Google Sheets page. Running this
	// on every page made an optional integration able to break the dashboard.
	if($(".lx-table-spreadsheets").length){
		getGoogleSheetOrders();
	}
	getAllNotifs();
	window.setInterval(function(){
		getAllNotifs();
	},5000);
	if(Notification.permission === "default"){
		if($("#my-notification-button").length){
			$("#my-notification-button").trigger("click");
		}			
	}
	if($("#salescontainer").length){
		loadChartData();
		loadKPI();		
		loadKPI2();		
		loadGains();
	}
	return false;
});

function getGoogleSheetOrders(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			action : 'getgooglesheetorders'
		},
		success : function(response){
			console.log(response);
		}
	});
}

$(".lx-main-menu > ul > ul > li > a").on("click",function(){
	$(".lx-main-menu ul ul ul").not($(this).parent().find("ul")).slideUp();
	$(this).parent().find("ul").slideToggle();
});

$(".lx-main-menu > ul > ul > li > i").on("click",function(){
	$(".lx-main-menu ul ul ul").not($(this).parent().find("ul")).slideUp();
	$(this).parent().find("ul").slideToggle();
});

function getAllNotifs(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			action : 'allnotifs'
		},
		success : function(response){
			if(response !== ""){
				var obj = eval ("(" + response + ")");
				if(obj.notifs[0].clients !== "0"){
					$(".lx-clients-notif").css("display","inline-block");
					$(".lx-clients-notif").text(obj.notifs[0].clients);
				}
				else{
					$(".lx-clients-notif").css("display","none");
				}
				if(obj.notifs[0].confirmation !== "0"){
					$(".lx-confirmation-notif").css("display","inline-block");
					$(".lx-confirmation-notif").text(obj.notifs[0].confirmation);
				}
				else{
					$(".lx-confirmation-notif").css("display","none");
				}
				if(obj.notifs[0].commands !== "0"){
					$(".lx-commands-notif").css("display","inline-block");
					$(".lx-commands-notif").text(obj.notifs[0].commands);
				}
				else{
					$(".lx-commands-notif").css("display","none");
				}
				if(obj.notifs[0].shipped !== "0"){
					$(".lx-shipped-notif").css("display","inline-block");
					$(".lx-shipped-notif").text(obj.notifs[0].shipped);
				}
				else{
					$(".lx-shipped-notif").css("display","none");
				}
				if(obj.notifs[0].today !== "0"){
					$(".lx-today-notif").css("display","inline-block");
					$(".lx-today-notif").text(obj.notifs[0].today);
				}
				else{
					$(".lx-today-notif").css("display","none");
				}
				if(obj.notifs[0].bra !== "0"){
					$(".lx-bra-notif").css("display","inline-block");
					$(".lx-bra-notif").text(obj.notifs[0].bra);
				}
				else{
					$(".lx-bra-notif").css("display","none");
				}
				if(obj.notifs[0].shipments !== "0"){
					$(".lx-shipments-notif").css("display","inline-block");
					$(".lx-shipments-notif").text(obj.notifs[0].shipments);
				}
				else{
					$(".lx-shipments-notif").css("display","none");
				}
				if(obj.notifs[0].fctclient !== "0"){
					$(".lx-fctclient-notif").css("display","inline-block");
					$(".lx-fctclient-notif").text(obj.notifs[0].fctclient);
				}
				else{
					$(".lx-fctclient-notif").css("display","none");
				}
				if(obj.notifs[0].fctdlm !== "0"){
					$(".lx-fctdlm-notif").css("display","inline-block");
					$(".lx-fctdlm-notif").text(obj.notifs[0].fctdlm);
				}
				else{
					$(".lx-fctdlm-notif").css("display","none");
				}
			}
		}
	});
}

function onManageWebPushSubscriptionButtonClicked(event) {
	getSubscriptionState().then(function(state) {
		if (state.isPushEnabled) {
			/* Subscribed, opt them out */
			OneSignal.setSubscription(false);
		} else {
			if (state.isOptedOut) {
				/* Opted out, opt them back in */
				OneSignal.setSubscription(true);
			} else {
				/* Unsubscribed, subscribe them */
				OneSignal.registerForPushNotifications();
			}
		}
	});
	event.preventDefault();
}
function updateMangeWebPushSubscriptionButton(buttonSelector) {
	var hideWhenSubscribed = false;
	var subscribeText = "Oui";
	var unsubscribeText = "Unsubscribe";
	getSubscriptionState().then(function(state) {
		var buttonText = !state.isPushEnabled || state.isOptedOut ? subscribeText : unsubscribeText;
		var element = document.querySelector(buttonSelector);
		if (element === null) {
			return;
		}
		element.removeEventListener('click', onManageWebPushSubscriptionButtonClicked);
		element.addEventListener('click', onManageWebPushSubscriptionButtonClicked);
		//element.textContent = buttonText;
		if (state.hideWhenSubscribed && state.isPushEnabled) {
			element.style.display = "none";
		} else {
			element.style.display = "";
		}
	});
}

function getSubscriptionState() {
	return Promise.all([
		OneSignal.isPushNotificationsEnabled(),
		OneSignal.isOptedOut()
	]).then(function(result) {
		var isPushEnabled = result[0];
		var isOptedOut = result[1];
		return {
			isPushEnabled: isPushEnabled,
			isOptedOut: isOptedOut
		};
	});
}

var buttonSelector = "#my-notification-button";
/* This example assumes you've already initialized OneSignal */
if (typeof OneSignal !== 'undefined') {
	OneSignal.push(function() {
		// If we're on an unsupported browser, do nothing
		if (!OneSignal.isPushNotificationsSupported()) {
			return;
		}
		updateMangeWebPushSubscriptionButton(buttonSelector);
		OneSignal.on("subscriptionChange", function(isSubscribed) {
			/* If the user's subscription state changes during the page's session, update the button text */
			updateMangeWebPushSubscriptionButton(buttonSelector);
			OneSignal.getUserId(function(userId) {
				var ajaxurl = "ajax.php";
				$.ajax({
					url : ajaxurl,
					type : 'post',
					data : {
						player : userId,
						action : 'saveplayer'
					},
					success : function(response){

					}		
				});				
			});
		});
	});
}

function loadChartData(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			client : $("#client").val(),
			dlm : $("#dlm").val(),
			city : $("#city").val(),
			product : $("#product").val(),
			datestart : $("#datestart").val(),
			dateend : $("#dateend").val(),
			action : 'loadchartdata'
		},
		success : function(response){
			var data = response.split("|");
			chartdates = data[0].split(",");
			chartdelivered = data[1].split(",").map(Number);
			chartcanceled = data[2].split(",").map(Number);
			loadChart();	
		}
	});
}

function loadChart(){
	Highcharts.setOptions({
		colors: ['#7EC855','#CC0000'],
	});
	Highcharts.chart('salescontainer', {
		chart: {
			type: 'line',
			height: 300,
			backgroundColor: null
		},
		title: {
			text: ''
		},
		yAxis: {
			title: {
				text: ''
			},
			alternateGridColor: '#FBFBFB'
		},
		xAxis: {
			categories: chartdates,
		},
		series: [{
			showInLegend: false,
			name: 'Livré',
			data: chartdelivered
		},
		{
			showInLegend: false,
			name: 'Annulé & Refusé',
			data: chartcanceled
		}],
		credits: {
			 enabled: false
		},
		responsive: {
			rules: [{
				condition: {
					maxWidth: 500
				},
				chartOptions: {
					legend: {
						layout: 'horizontal',
						align: 'center',
						verticalAlign: 'bottom'
					}
				}
			}]
		}
	});	
}

$("#client_kpi,#dlm_kpi,#city_kpi,#product_kpi,#worker_kpi").on("change",function(){
	loadKPI();
});

$("#dlm_kpi2").on("change",function(){
	loadKPI2();
});

function loadKPI(){
	if($(".lx-kpis .lx-loading").length === 0){
		$(".lx-kpis").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			client : $("#client_kpi").val(),
			worker : $("#worker_kpi").val(),
			dlm : $("#dlm_kpi").val(),
			city : $("#city_kpi").val(),
			product : $("#product_kpi").val(),
			datestart : $("#datestart_kpi").val(),
			dateend : $("#dateend_kpi").val(),
			action : 'loadkpi'
		},
		success : function(response){
			$(".lx-kpis").html(response);
		}
	});	
}

function loadKPI2(){
	if($(".lx-kpis2 .lx-loading").length === 0){
		$(".lx-kpis2").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			dlm : $("#dlm_kpi2").val(),
			datestart : $("#datestart_kpi2").val(),
			dateend : $("#dateend_kpi2").val(),
			action : 'loadkpi2'
		},
		success : function(response){
			$(".lx-kpis2").html(response);
		}
	});	
}

function loadGains(){
	if($(".lx-profit .lx-loading").length === 0){
		$(".lx-profit").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			datestart : $("#datestart_profit").val(),
			dateend : $("#dateend_profit").val(),
			action : 'loadgains'
		},
		success : function(response){
			$(".lx-profit").html(response);
		}
	});	
}

$(".lx-stats-bloc ul li a").on("click",function(){
	$(this).parents("ul").find("a").removeClass("active");
	$(this).addClass("active");
	$(this).parents("div.lx-stats-bloc").find("table[data-tab]").hide();
	$(this).parents("div.lx-stats-bloc").find("table[data-tab='"+$(this).attr("data-tab")+"']").show();
});

$("#product_rate").on("change",function(){
	loadDLMRate();
	loadCityRate();
});

function loadDLMRate(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			product : $("#product_rate").val(),
			datestart : $("#datestart_rate").val(),
			dateend : $("#dateend_rate").val(),
			action : 'loaddlmrate'
		},
		success : function(response){
			$(".lx-table-dlm-rate").html(response);
		}
	});	
}

function loadCityRate(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			product : $("#product_rate").val(),
			datestart : $("#datestart_rate").val(),
			dateend : $("#dateend_rate").val(),
			action : 'loadcityrate'
		},
		success : function(response){
			$(".lx-table-city-rate").html(response);
		}
	});	
}

$(".lx-mobile-menu").on("click",function(){
	$(".lx-main-leftside").css("left","0px");
});

$(".lx-mobile-menu-hide").on("click",function(){
	$(".lx-main-leftside").css("left","-230px");
});

$(".lx-login .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	$(".lx-login form").submit();
});

$(document).on("keyup",function(e) {
    if(e.keyCode == 13) {	
		if($(".lx-login .lx-submit a").length){
			$(".lx-login .lx-submit a").trigger("click");
		}
		if($(".lx-search-keyword").length){
			$(".lx-search-keyword").trigger("click");
		}
    }
});

$(".lx-login .lx-textfield label i").on("click",function(){
	if($(this).attr("class") === "fa fa-eye-slash"){
		$(this).attr("class","fa fa-eye").css("color","#39add1");
		$(this).prev("input").attr("type","text");
	}
	else{
		$(this).attr("class","fa fa-eye-slash").css("color","#CCCCCC");
		$(this).prev("input").attr("type","password");
	}
});

function _(el){
	return document.getElementById(el);
}

$("#medias").on("change",function(){
	uploadsImages(_("medias").files[0]);
});

function uploadsImages(picture){
	var file = picture;
	var formdata = new FormData();
	formdata.append("file0", file);
	var ajax = new XMLHttpRequest();
	ajax.addEventListener("load", completeHandler1, false);
	ajax.open("POST", "file_upload_parser.php");
	ajax.send(formdata);
	function completeHandler1(event){
		if (ajax.readyState === 4 && (ajax.status === 200 || ajax.status === 0)) {
			if(ajax.responseText !== ""){
				var html = '<img src="uploads/cropped_'+ajax.responseText+'" />';
				$("input[name='picture']").val(ajax.responseText);
				$(".lx-medias-item").html(html);
			}
		}
	}
}

$("#importcommands").on("change",function(){
	$(".lx-importer").css({"border-color":"green","background":"#d1e8cc"});
	$(".lx-importer span").text($(this).val().replace(/C:\\fakepath\\/i, '')).css({"font-weight":"bold","color":"green"});
});

$("#importform .lx-submit a").on("click",function(){
	uploadsXLSCommand(_("importcommands"),_("importclient").value,_("importformat").value,$("#importform input[name='state']").val(),$("#importform input[name='phase']").val(),$("#importform input[name='confirmed']").val());
});

function uploadsXLSCommand(file,client,format,state,phase,confirmed){
	if(file.value !== "" && client !== "" && format !== ""){
		if($("#importform .lx-submit a").find("i").length === 0){
			$("#importform .lx-submit a").prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
		}
		$("*[data-required]").removeAttr("style");
		var formdata = new FormData();
		formdata.append("file2", file.files[0]);
		formdata.append("client", client);
		formdata.append("format", format);
		formdata.append("state", state);
		formdata.append("phase", phase);
		formdata.append("confirmed", confirmed);
		var ajax = new XMLHttpRequest();
		ajax.addEventListener("load", completeHandler, false);
		ajax.open("POST", "file_upload_parser.php");
		ajax.send(formdata);
		function completeHandler(event){
			if (ajax.readyState === 4 && (ajax.status === 200 || ajax.status === 0)) {
				console.log(ajax.responseText);
				$("#importform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadRamassage($(".lx-pagination ul").attr("data-state"));
				$(".lx-importer").removeAttr("style");
				$(".lx-importer span").text("Choisissez un fichier (excel)").removeAttr("style");
				$(".lx-error-import").css("display","inline-block");
			}
		}			
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir tous les champs !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);		
	}
}

$("#importstates").on("change",function(){
	$(".lx-importer").css({"border-color":"green","background":"#d1e8cc"});
	$(".lx-importer span").text($(this).val().replace(/C:\\fakepath\\/i, '')).css({"font-weight":"bold","color":"green"});
});

$("#importstateform .lx-submit a").on("click",function(){
	uploadsXLSState(document.getElementById("importstates"));
});

function uploadsXLSState(file){
	if(file.value !== ""){
		if($("#importstateform .lx-submit a").find("i").length === 0){
			$("#importstateform .lx-submit a").prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
		}
		$("*[data-required]").removeAttr("style");
		var formdata = new FormData();
		formdata.append("file4", file.files[0]);
		var ajax = new XMLHttpRequest();
		ajax.addEventListener("load", completeHandler, false);
		ajax.open("POST", "file_upload_parser.php");
		ajax.send(formdata);
		function completeHandler(event){
			if (ajax.readyState === 4 && (ajax.status === 200 || ajax.status === 0)) {
				$("#importstateform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadCommands($(".lx-pagination ul").attr("data-state"));
				$(".lx-importer").removeAttr("style");
				$(".lx-importer span").text("Choisissez un fichier (excel)").removeAttr("style");
			}
		}			
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir tous les champs !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);		
	}
}

$("#importfeesform .lx-submit a").on("click",function(){
	uploadsFees(_("importfees"),_("importclient").value,$("#typeuser").val());
});

function uploadsFees(file,client,type){
	if(file.value !== "" && client !== ""){
		$("*[data-required]").removeAttr("style");
		var formdata = new FormData();
		formdata.append("file3", file.files[0]);
		formdata.append("client", client);
		formdata.append("type", type);
		var ajax = new XMLHttpRequest();
		ajax.addEventListener("load", completeHandler, false);
		ajax.open("POST", "file_upload_parser.php");
		ajax.send(formdata);
		function completeHandler(event){
			if (ajax.readyState === 4 && (ajax.status === 200 || ajax.status === 0)) {
				console.log(ajax.responseText);
				$(".lx-popup-content > a > .material-icons").trigger("click");
				if($(".lx-pagination ul").attr("data-state") === "clientfees"){
					loadClientfees($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-state") === "shippingfees"){
					loadShippingfees($(".lx-pagination ul").attr("data-state"));
				}				
			}
		}			
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir tous les champs !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);		
	}
}

$("body").delegate(".lx-first-tr i","click",function(){
	filterClicked = "yes";
	$(".lx-keyword input[name='sortby']").val($(this).attr("data-sort"));
	if($(".lx-keyword input[name='orderby']").val() === "ASC"){
		$(".lx-keyword input[name='orderby']").val("DESC");
	}
	else{
		$(".lx-keyword input[name='orderby']").val("ASC");
	}
	if($(".lx-pagination ul").attr("data-table") === "users"){
		loadUsers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "cities"){
		loadCities($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "dlm"){
		loadDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "subdlm"){
		loadSubDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
		loadShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
		loadGShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "trackingstates"){		loadTrackingstates($(".lx-pagination ul").attr("data-state"));	}		if($(".lx-pagination ul").attr("data-table") === "shipramassages"){		loadShipRamassages($(".lx-pagination ul").attr("data-state"));	}
	if($(".lx-pagination ul").attr("data-table") === "clients"){
		loadClients($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "workers"){
		loadWorkers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "clientfees"){
		loadClientfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shipments"){
		loadShipments($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "stocks"){
		loadStocks($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
		loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "bls"){
		loadBLS($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "factures"){
		loadFactures($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "notices"){
		loadNotices($(".lx-pagination ul").attr("data-state"));
	}	
	if($(".lx-pagination ul").attr("data-table") === "expenses"){
		loadExpenses($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "reclamations"){
		loadReclamations($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
		loadSMSDevices($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
		loadSMSModels($(".lx-pagination ul").attr("data-state"));
	}	
});

$(".lx-new-notice").on("click",function(){
	$("#noticesform textarea[name='description']").val("");
	$("#noticesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-notice","click",function(){
	$("#noticesform textarea[name='description']").val($(this).attr("data-description"));
	$("#noticesform input[name='id']").val($(this).attr("data-id"));
});

$("#noticesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#noticesform textarea[name='description']"));
	if(isNotEmpty($("#noticesform textarea[name='description']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#noticesform input[name='id']").val(),
				description : $("#noticesform textarea[name='description']").val(),
				action : 'addnotice'
			},
			success : function(response){
				$("#noticesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadNotices("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Annonce enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#noticesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-notice","click",function(){
	filterClicked = "yes";
	loadNotices("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-notice","click",function(){
	filterClicked = "yes";
	loadNotices("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-notice","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-notice","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorenotice'
		},
		success : function(response){
			loadNotices("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-notice","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletenoticepermanently'
		},
		success : function(response){
			loadNotices("0");
		}
	});
});

function loadNotices(state){
	if($(".lx-table-notices .lx-loading").length === 0){
		$(".lx-table-notices").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadnotices'
		},
		success : function(response){
			$(".lx-table-notices .lx-loading").remove();
			$(".lx-table-notices").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("#usersform input[name='roles']").click(function(){
	$("#usersform input[name='rolestext']").val("");
	$("#usersform input[name='roles']").each(function(){
		if($(this).prop("checked") === true){
			$("#usersform input[name='rolestext']").val($("#usersform input[name='rolestext']").val() + "," + $(this).val());
		}
	});
});

$(".lx-new-user").on("click",function(){
	$("#usersform input[name='fullname']").val("");
	$("#usersform input[name='email']").val("").prop("readonly",false).css("cursor","initial");
	$("#usersform input[name='password']").val("");
	$("#usersform input[name='phone']").val("");
	$("#usersform input[name='roles']").each(function(){
		$(this).prop("checked",false);
	})
	$("#usersform input[name='rolestext']").val("");
	$("#usersform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-user","click",function(){
	$("#usersform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#usersform input[name='email']").val($(this).attr("data-email"));
	$("#usersform input[name='password']").val($(this).attr("data-password"));
	$("#usersform input[name='phone']").val($(this).attr("data-phone"));
	$("#usersform input[name='rolestext']").val($(this).attr("data-roles"));
	$("#usersform input[name='roles']").each(function(){
		var val = $("#usersform input[name='rolestext']").val().indexOf($(this).val());
		if(val !== -1){
			$(this).prop("checked",true);
		}
		else{
			$(this).prop("checked",false);
		}
	})
	$("#usersform input[name='id']").val($(this).attr("data-id"));
});

$("#usersform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#usersform input[name='fullname']"));
	isPhone($("#usersform input[name='phone']"));
	isEmail($("#usersform input[name='email']"));
	isPassword($("#usersform input[name='password']"));
	if(isNotEmpty($("#usersform input[name='fullname']"))
	&& isPhone($("#usersform input[name='phone']"))
	&& isEmail($("#usersform input[name='email']"))
	&& isPassword($("#usersform input[name='password']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#usersform input[name='id']").val(),
				fullname : $("#usersform input[name='fullname']").val(),
				email : $("#usersform input[name='email']").val(),
				password : $("#usersform input[name='password']").val(),
				phone : $("#usersform input[name='phone']").val(),
				roles : $("#usersform input[name='rolestext']").val(),
				action : 'adduser'
			},
			success : function(response){
				$("#usersform .lx-submit a i").remove();
				if(response === "Email exist déja !!"){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Email exist déja !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
				else{
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadUsers("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			}
		});
	}
	else{
		$("#usersform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-user","click",function(){
	filterClicked = "yes";
	loadUsers("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-user","click",function(){
	filterClicked = "yes";
	loadUsers("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-user","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-user","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoreuser'
		},
		success : function(response){
			loadUsers("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-user","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteuserpermanently'
		},
		success : function(response){
			loadUsers("0");
		}
	});
});

function loadUsers(state){
	if($(".lx-table-users .lx-loading").length === 0){
		$(".lx-table-users").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadusers'
		},
		success : function(response){
			$(".lx-table-users .lx-loading").remove();
			$(".lx-table-users").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("#accountform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#accountform input[name='fullname']"));
	isPhone($("#accountform input[name='phone']"));
	if(isNotEmpty($("#accountform input[name='fullname']"))
	&& isPhone($("#accountform input[name='phone']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#accountform input[name='id']").val(),
				fullname : $("#accountform input[name='fullname']").val(),
				picture : $("#accountform input[name='picture']").val(),
				phone : $("#accountform input[name='phone']").val(),
				action : 'editaccount'
			},
			success : function(response){
				$("#accountform .lx-submit a i").remove();
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Informations modifié<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#accountform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#passwordform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : $("#passwordform input[name='id']").val(),
			oldpassword : $("#passwordform input[name='oldpassword']").val(),
			newpassword1 : $("#passwordform input[name='newpassword1']").val(),
			newpassword2 : $("#passwordform input[name='newpassword2']").val(),
			action : 'editpassword'
		},
		success : function(response){
			$("#passwordform .lx-submit a i").remove();
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			if(response === "1"){
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Mot de pass modifié<i class="material-icons">close</i></p></div>');
			}
			else if(response === "2"){
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Vous devez remplir tous les champs<i class="material-icons">close</i></p></div>');
			}
			else if(response === "3"){
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Ancien mot de passe incorrect<i class="material-icons">close</i></p></div>');
			}
			else if(response === "4"){
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Nouveau mot de passe non identiques<i class="material-icons">close</i></p></div>');
			}
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);
		}
	});
});

$("#settingsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	var rowcolor = 0;
	if($("#settingsform input[name='rowcolor']").prop("checked") === true){
		rowcolor = 1;
	}
	var sepdelivered = 0;
	if($("#settingsform input[name='sepdelivered']").prop("checked") === true){
		sepdelivered = 1;
	}
	var simplestats = 0;
	if($("#settingsform input[name='simplestats']").prop("checked") === true){
		simplestats = 1;
	}
	var requirednote = 0;
	if($("#settingsform input[name='requirednote']").prop("checked") === true){
		requirednote = 1;
	}
	var reseller = 0;
	if($("#settingsform input[name='reseller']").prop("checked") === true){
		reseller = 1;
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : $("#settingsform input[name='id']").val(),
			logo : $("#settingsform input[name='picture']").val(),
			appname : $("#settingsform input[name='appname']").val(),
			cmdprefix : $("#settingsform input[name='cmdprefix']").val(),
			rowcolor : rowcolor,
			sepdelivered : sepdelivered,
			simplestats : simplestats,
			requirednote : requirednote,
			reseller : reseller,
			nbrows : $("#settingsform select[name='nbrows']").val(),
			currency : $("#settingsform input[name='currency']").val(),
			confirmationfees : $("#settingsform input[name='confirmationfees']").val(),
			action : 'editsettings'
		},
		success : function(response){
			$("#settingsform .lx-submit a i").remove();
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Préference modifié<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);
		}
	});
});

$(".lx-new-city").on("click",function(){
	$("#citiesform input[name='city']").val("");
	$("#citiesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-city","click",function(){
	$("#citiesform input[name='city']").val($(this).attr("data-city"));
	$("#citiesform input[name='id']").val($(this).attr("data-id"));
});

$("#citiesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#citiesform input[name='city']"));
	if(isNotEmpty($("#citiesform input[name='city']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#citiesform input[name='id']").val(),
				city : $("#citiesform input[name='city']").val(),
				action : 'addcity'
			},
			success : function(response){
				$("#citiesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadCities("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Ville enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#citiesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-cities","click",function(){
	filterClicked = "yes";
	loadCities("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-cities","click",function(){
	filterClicked = "yes";
	loadCities("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-city","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-recity-city","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'recitiecity'
		},
		success : function(response){
			loadCities("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-city","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletecitypermanently'
		},
		success : function(response){
			loadCities("0");
		}
	});
});

function loadCities(state){
	if($(".lx-table-cities .lx-loading").length === 0){
		$(".lx-table-cities").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadcities'
		},
		success : function(response){
			$(".lx-table-cities .lx-loading").remove();
			$(".lx-table-cities").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("#dlmform input[name='roles']").click(function(){
	$("#dlmform input[name='rolestext']").val("");
	$("#dlmform input[name='roles']").each(function(){
		if($(this).prop("checked") === true){
			$("#dlmform input[name='rolestext']").val($("#dlmform input[name='rolestext']").val() + "," + $(this).val());
		}
	});
});

$(".lx-new-dlm").on("click",function(){
	$("#dlmform input[name='fullname']").val("");
	$("#dlmform input[name='email']").val("").prop("readonly",false).css("cursor","initial");
	$("#dlmform input[name='password']").val("");
	$("#dlmform input[name='phone']").val("");
	$("#dlmform input[name='stockout']").val("");
	$("#dlmform input[name='emailstockout']").val("");
	$("#dlmform input[name='roles']").each(function(){
		$(this).prop("checked",false);
	})
	$("#dlmform input[name='rolestext']").val("");
	$("#dlmform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-dlm","click",function(){
	$("#dlmform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#dlmform input[name='email']").val($(this).attr("data-email"));
	$("#dlmform input[name='password']").val($(this).attr("data-password"));
	$("#dlmform input[name='phone']").val($(this).attr("data-phone"));
	$("#dlmform input[name='stockout']").val($(this).attr("data-stockout"));
	$("#dlmform input[name='emailstockout']").val($(this).attr("data-emailstockout"));
	$("#dlmform input[name='rolestext']").val($(this).attr("data-roles"));
	$("#dlmform input[name='roles']").each(function(){
		var val = $("#dlmform input[name='rolestext']").val().indexOf($(this).val());
		if(val !== -1){
			$(this).prop("checked",true);
		}
		else{
			$(this).prop("checked",false);
		}
	})
	$("#dlmform input[name='id']").val($(this).attr("data-id"));
});

$("#dlmform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#dlmform input[name='fullname']"));
	isPhone($("#dlmform input[name='phone']"));
	isEmail($("#dlmform input[name='email']"));
	isPassword($("#dlmform input[name='password']"));
	if(isNotEmpty($("#dlmform input[name='fullname']"))
	&& isPhone($("#dlmform input[name='phone']"))
	&& isEmail($("#dlmform input[name='email']"))
	&& isPassword($("#dlmform input[name='password']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#dlmform input[name='id']").val(),
				fullname : $("#dlmform input[name='fullname']").val(),
				email : $("#dlmform input[name='email']").val(),
				password : $("#dlmform input[name='password']").val(),
				phone : $("#dlmform input[name='phone']").val(),
				stockout : $("#dlmform input[name='stockout']").val(),
				emailstockout : $("#dlmform input[name='emailstockout']").val(),
				roles : $("#dlmform input[name='rolestext']").val(),
				action : 'adddlm'
			},
			success : function(response){
				$("#dlmform .lx-submit a i").remove();
				if(response === "Email exist déja !!"){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Email exist déja !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
				else{
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadDLM("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Livreur enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			}
		});
	}
	else{
		$("#dlmform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-dlm","click",function(){
	filterClicked = "yes";
	loadDLM("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-dlm","click",function(){
	filterClicked = "yes";
	loadDLM("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-dlm","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-dlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoredlm'
		},
		success : function(response){
			loadDLM("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-dlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletedlmpermanently'
		},
		success : function(response){
			loadDLM("0");
		}
	});
});

function loadDLM(state){
	if($(".lx-table-dlm .lx-loading").length === 0){
		$(".lx-table-dlm").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loaddlm'
		},
		success : function(response){
			$(".lx-table-dlm .lx-loading").remove();
			$(".lx-table-dlm").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-subdlm").on("click",function(){
	$("#subdlmform input[name='fullname']").val("");
	$("#subdlmform input[name='email']").val("").prop("readonly",false).css("cursor","initial");
	$("#subdlmform input[name='password']").val("");
	$("#subdlmform input[name='phone']").val("");
	$("#subdlmform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-subdlm","click",function(){
	$("#subdlmform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#subdlmform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#subdlmform input[name='email']").val($(this).attr("data-email")).prop("readonly",true).css("cursor","not-allowed");
	$("#subdlmform input[name='password']").val($(this).attr("data-password"));
	$("#subdlmform input[name='phone']").val($(this).attr("data-phone"));
	$("#subdlmform input[name='id']").val($(this).attr("data-id"));
});

$("#subdlmform .lx-submit a").on("click",function(){
	isNotEmpty($("#subdlmform select[name='dlm']"));
	isNotEmpty($("#subdlmform input[name='fullname']"));
	isPhone($("#subdlmform input[name='phone']"));
	isEmail($("#subdlmform input[name='email']"));
	isPassword($("#subdlmform input[name='password']"));
	if(isNotEmpty($("#subdlmform input[name='fullname']"))
	&& isNotEmpty($("#subdlmform select[name='dlm']"))
	&& isPhone($("#subdlmform input[name='phone']"))
	&& isEmail($("#subdlmform input[name='email']"))
	&& isPassword($("#subdlmform input[name='password']"))){
		if($(this).attr("class") !== "lx-disabled"){
			$(this).attr("class","lx-disabled");
			if($(this).find("i").length === 0){
				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
			}
			var ajaxurl = "ajax.php";
			$.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					id : $("#subdlmform input[name='id']").val(),
					dlm : $("#subdlmform select[name='dlm']").val(),
					fullname : $("#subdlmform input[name='fullname']").val(),
					email : $("#subdlmform input[name='email']").val(),
					password : $("#subdlmform input[name='password']").val(),
					phone : $("#subdlmform input[name='phone']").val(),
					action : 'addsubdlm'
				},
				success : function(response){
					$("#subdlmform .lx-submit a").attr("class","");
					$("#subdlmform .lx-submit a i").remove();
					if(response === "Email exist déja !!"){
						$(".lx-floating-response").remove();
						window.clearTimeout(timer);
						$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Email exist déja !!<i class="material-icons">close</i></p></div>');
						$(".lx-floating-response").fadeIn();
						timer = window.setTimeout(function(){
							$(".lx-floating-response").fadeOut();
						},5000);
					}
					else{
						$(".lx-popup-content > a > .material-icons").trigger("click");
						loadSubDLM("1");
						$(".lx-floating-response").remove();
						window.clearTimeout(timer);
						$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Bien enregistré<i class="material-icons">close</i></p></div>');
						$(".lx-floating-response").fadeIn();
						timer = window.setTimeout(function(){
							$(".lx-floating-response").fadeOut();
						},5000);
					}
				}
			});
		}
	}
	else{
		$("#subdlmform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-subdlm","click",function(){
	filterClicked = "yes";
	loadSubDLM("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-subdlm","click",function(){
	filterClicked = "yes";
	loadSubDLM("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-subdlm","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-subdlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoresubdlm'
		},
		success : function(response){
			loadSubDLM("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-subdlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletesubdlmpermanently'
		},
		success : function(response){
			loadSubDLM("0");
		}
	});
});

function loadSubDLM(state){
	if($(".lx-table-subdlm .lx-loading").length === 0){
		$(".lx-table-subdlm").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			dlm : $("#dlm").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadsubdlm'
		},
		success : function(response){
			$(".lx-table-subdlm .lx-loading").remove();
			$(".lx-table-subdlm").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-shippingfee").on("click",function(){
	$("#shippingfeesform select[name='dlm']").val("");
	$("#shippingfeesform select[name='city']").val("");
	$("#shippingfeesform input[name='deliveredfees']").val("");
	$("#shippingfeesform input[name='refusedfees']").val("");
	$("#shippingfeesform input[name='id']").val("0");
	toDropDown();
});

$("body").delegate(".lx-edit-shippingfee","click",function(){
	$("#shippingfeesform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#shippingfeesform select[name='city']").val($(this).attr("data-city"));
	$("#shippingfeesform input[name='deliveredfees']").val($(this).attr("data-deliveredfees"));
	$("#shippingfeesform input[name='refusedfees']").val($(this).attr("data-refusedfees"));
	$("#shippingfeesform input[name='id']").val($(this).attr("data-id"));
	toDropDown();
});

$("#shippingfeesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#shippingfeesform select[name='dlm']"));
	isNotEmpty($("#shippingfeesform select[name='city']"));
	isNumber($("#shippingfeesform input[name='deliveredfees']"));
	if(isNotEmpty($("#shippingfeesform select[name='dlm']"))
	&& isNotEmpty($("#shippingfeesform select[name='city']"))
	&& isNumber($("#shippingfeesform input[name='deliveredfees']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#shippingfeesform input[name='id']").val(),
				dlm : $("#shippingfeesform select[name='dlm']").val(),
				city : $("#shippingfeesform select[name='city']").val(),
				deliveredfees : $("#shippingfeesform input[name='deliveredfees']").val(),
				refusedfees : $("#shippingfeesform input[name='refusedfees']").val(),
				action : 'addshippingfee'
			},
			success : function(response){
				$("#shippingfeesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadShippingfees("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#shippingfeesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-shippingfee","click",function(){
	filterClicked = "yes";
	loadShippingfees("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-shippingfee","click",function(){
	filterClicked = "yes";
	loadShippingfees("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-shippingfee","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-reshippingfee-shippingfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'reshippingfeeshippingfee'
		},
		success : function(response){
			loadShippingfees("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-shippingfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteshippingfeepermanently'
		},
		success : function(response){
			loadShippingfees("0");
		}
	});
});

function loadShippingfees(state){
	if($(".lx-table-shippingfees .lx-loading").length === 0){
		$(".lx-table-shippingfees").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			dlm : $("#dlm").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadshippingfees'
		},
		success : function(response){
			$(".lx-table-shippingfees .lx-loading").remove();
			$(".lx-table-shippingfees").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("#trackingstatesform input[name='agents']").click(function(){
	$("#trackingstatesform input[name='agentstext']").val("");
	$("#trackingstatesform input[name='agents']").each(function(){
		if($(this).prop("checked") === true){
			$("#trackingstatesform input[name='agentstext']").val($("#trackingstatesform input[name='agentstext']").val() + "," + $(this).val());
		}
	});
});

$("#trackingstatesform input[name='phase']").click(function(){
	$("#trackingstatesform input[name='phasetext']").val("");
	$("#trackingstatesform input[name='phase']").each(function(){
		if($(this).prop("checked") === true){
			$("#trackingstatesform input[name='phasetext']").val($("#trackingstatesform input[name='phasetext']").val() + "," + $(this).val());
		}
	});
});

$("#trackingstatesform input[name='kpi']").click(function(){
	$("#trackingstatesform input[name='kpitext']").val("");
	$("#trackingstatesform input[name='kpi']").each(function(){
		if($(this).prop("checked") === true){
			$("#trackingstatesform input[name='kpitext']").val($("#trackingstatesform input[name='kpitext']").val() + "," + $(this).val());
		}
	});
});

$(".lx-new-trackingstate").on("click",function(){
	$("#trackingstatesform input[name='state']").val("").prop("readonly",false);
	$("#trackingstatesform input[name='color']").val("");
	$("#trackingstatesform input[name='agents']").each(function(){
		$(this).prop("checked",false);
	});
	$("#trackingstatesform input[name='agentstext']").val("");
	$("#trackingstatesform input[name='phase']").each(function(){
		$(this).prop("checked",false);
	});
	$("#trackingstatesform input[name='phasetext']").val("");
	$("#trackingstatesform input[name='kpi']").each(function(){
		$(this).prop("checked",false);
	});
	$("#trackingstatesform input[name='kpitext']").val("");
	$("#trackingstatesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-trackingstate","click",function(){
	if($(this).attr("data-state") === "Livré" || $(this).attr("data-state") === "Annulé" || $(this).attr("data-state") === "Refusé" || $(this).attr("data-state") === "Change"){
		$("#trackingstatesform input[name='state']").val($(this).attr("data-state")).prop("readonly",true);
	}
	else{
		$("#trackingstatesform input[name='state']").val($(this).attr("data-state")).prop("readonly",false);
	}
	$("#trackingstatesform input[name='color']").val($(this).attr("data-color"));
	$("#trackingstatesform input[name='agentstext']").val($(this).attr("data-agents"));
	$("#trackingstatesform input[name='agents']").each(function(){
		var val = $("#trackingstatesform input[name='agentstext']").val().indexOf($(this).val());
		if(val !== -1){
			$(this).prop("checked",true);
		}
		else{
			$(this).prop("checked",false);
		}
	});
	$("#trackingstatesform input[name='phasetext']").val($(this).attr("data-phase"));
	$("#trackingstatesform input[name='phase']").each(function(){
		var val = $("#trackingstatesform input[name='phasetext']").val().indexOf($(this).val());
		if(val !== -1){
			$(this).prop("checked",true);
		}
		else{
			$(this).prop("checked",false);
		}
	});
	$("#trackingstatesform input[name='kpitext']").val($(this).attr("data-kpi"));
	$("#trackingstatesform input[name='kpi']").each(function(){
		var val = $("#trackingstatesform input[name='kpitext']").val().indexOf($(this).val());
		if(val !== -1){
			$(this).prop("checked",true);
		}
		else{
			$(this).prop("checked",false);
		}
	});
	$("#trackingstatesform input[name='id']").val($(this).attr("data-id"));
});

$("#trackingstatesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#trackingstatesform input[name='state']"));
	isNotEmpty($("#trackingstatesform input[name='color']"));
	if(isNotEmpty($("#trackingstatesform input[name='state']"))
	&& isNotEmpty($("#trackingstatesform input[name='color']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#trackingstatesform input[name='id']").val(),
				state : $("#trackingstatesform input[name='state']").val(),
				color : $("#trackingstatesform input[name='color']").val(),
				agents : $("#trackingstatesform input[name='agentstext']").val(),
				phase : $("#trackingstatesform input[name='phasetext']").val(),
				kpi : $("#trackingstatesform input[name='kpitext']").val(),
				action : 'addtrackingstate'
			},
			success : function(response){
				$("#trackingstatesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadTrackingstates("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#trackingstatesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-trackingstate","click",function(){
	filterClicked = "yes";
	loadTrackingstates("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-trackingstate","click",function(){
	filterClicked = "yes";
	loadTrackingstates("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-trackingstate","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-trackingstate","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoretrackingstate'
		},
		success : function(response){
			loadTrackingstates("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-trackingstate","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletetrackingstatepermanently'
		},
		success : function(response){
			loadTrackingstates("0");
		}
	});
});

function loadTrackingstates(state){
	if($(".lx-table-trackingstates .lx-loading").length === 0){
		$(".lx-table-trackingstates").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadtrackingstates'
		},
		success : function(response){
			$(".lx-table-trackingstates .lx-loading").remove();
			$(".lx-table-trackingstates").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-client").on("click",function(){
	$("#clientsform input[name='fullname']").val("");
	$("#clientsform input[name='email']").val("").prop("readonly",false).css("cursor","initial");
	$("#clientsform input[name='password']").val("");
	$("#clientsform input[name='phone']").val("");
	$("#clientsform input[name='sav']").val("");
	$("#clientsform select[name='city']").val("");
	$("#clientsform input[name='cin']").val("");
	$("#clientsform input[name='store']").val("");
	$("#clientsform select[name='bank']").val("");
	$("#clientsform input[name='rib']").val("");
	$("#clientsform input[name='stockout']").val("");
	$("#clientsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-client","click",function(){
	$("#clientsform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#clientsform input[name='email']").val($(this).attr("data-email"));
	$("#clientsform input[name='password']").val($(this).attr("data-password"));
	$("#clientsform input[name='phone']").val($(this).attr("data-phone"));
	$("#clientsform input[name='sav']").val($(this).attr("data-sav"));
	$("#clientsform select[name='city']").val($(this).attr("data-city"));
	$("#clientsform input[name='cin']").val($(this).attr("data-cin"));
	$("#clientsform input[name='store']").val($(this).attr("data-store"));
	$("#clientsform select[name='bank']").val($(this).attr("data-bank"));
	$("#clientsform input[name='rib']").val($(this).attr("data-rib"));
	$("#clientsform input[name='stockout']").val($(this).attr("data-stockout"));
	$("#clientsform input[name='id']").val($(this).attr("data-id"));
});

$("#clientsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#clientsform input[name='fullname']"));
	isPhone($("#clientsform input[name='phone']"));
	isEmail($("#clientsform input[name='email']"));
	isPassword($("#clientsform input[name='password']"));
	if(isNotEmpty($("#clientsform input[name='fullname']"))
	&& isPhone($("#clientsform input[name='phone']"))
	&& isEmail($("#clientsform input[name='email']"))
	&& isPassword($("#clientsform input[name='password']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#clientsform input[name='id']").val(),
				fullname : $("#clientsform input[name='fullname']").val(),
				email : $("#clientsform input[name='email']").val(),
				password : $("#clientsform input[name='password']").val(),
				phone : $("#clientsform input[name='phone']").val(),
				sav : $("#clientsform input[name='sav']").val(),
				city : $("#clientsform select[name='city']").val(),
				cin : $("#clientsform input[name='cin']").val(),
				store : $("#clientsform input[name='store']").val(),
				bank : $("#clientsform select[name='bank']").val(),
				rib : $("#clientsform input[name='rib']").val(),
				stockout : $("#clientsform input[name='stockout']").val(),
				action : 'addclient'
			},
			success : function(response){
				$("#clientsform .lx-submit a i").remove();
				if(response === "Email exist déja !!"){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Email exist déja !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
				else{
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadClients("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Employé enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			}
		});
	}
	else{
		$("#clientsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-client","click",function(){
	filterClicked = "yes";
	loadClients("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-client","click",function(){
	filterClicked = "yes";
	loadClients("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-client","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-client","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoreclient'
		},
		success : function(response){
			loadClients("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-client","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteclientpermanently'
		},
		success : function(response){
			loadClients("0");
		}
	});
});

function loadClients(state){
	if($(".lx-table-clients .lx-loading").length === 0){
		$(".lx-table-clients").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),	
			active : $("#active").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadclients'
		},
		success : function(response){
			$(".lx-table-clients .lx-loading").remove();
			$(".lx-table-clients").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-worker").on("click",function(){
	$("#workersform input[name='fullname']").val("");
	$("#workersform input[name='email']").val("").prop("readonly",false).css("cursor","initial");
	$("#workersform input[name='password']").val("");
	$("#workersform input[name='phone']").val("");
	$("#workersform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-worker","click",function(){
	$("#workersform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#workersform input[name='email']").val($(this).attr("data-email"));
	$("#workersform input[name='password']").val($(this).attr("data-password"));
	$("#workersform input[name='phone']").val($(this).attr("data-phone"));
	$("#workersform input[name='id']").val($(this).attr("data-id"));
});

$("#workersform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#workersform input[name='fullname']"));
	isPhone($("#workersform input[name='phone']"));
	isEmail($("#workersform input[name='email']"));
	isPassword($("#workersform input[name='password']"));
	if(isNotEmpty($("#workersform input[name='fullname']"))
	&& isPhone($("#workersform input[name='phone']"))
	&& isEmail($("#workersform input[name='email']"))
	&& isPassword($("#workersform input[name='password']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#workersform input[name='id']").val(),
				fullname : $("#workersform input[name='fullname']").val(),
				email : $("#workersform input[name='email']").val(),
				password : $("#workersform input[name='password']").val(),
				phone : $("#workersform input[name='phone']").val(),
				action : 'addworker'
			},
			success : function(response){
				$("#workersform .lx-submit a i").remove();
				if(response === "Email exist déja !!"){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Email exist déja !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
				else{
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadWorkers("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Employé enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			}
		});
	}
	else{
		$("#workersform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-worker","click",function(){
	filterClicked = "yes";
	loadWorkers("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-worker","click",function(){
	filterClicked = "yes";
	loadWorkers("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-worker","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-worker","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoreworker'
		},
		success : function(response){
			loadWorkers("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-worker","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteworkerpermanently'
		},
		success : function(response){
			loadWorkers("0");
		}
	});
});

function loadWorkers(state){
	if($(".lx-table-workers .lx-loading").length === 0){
		$(".lx-table-workers").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadworkers'
		},
		success : function(response){
			$(".lx-table-workers .lx-loading").remove();
			$(".lx-table-workers").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-clientfee").on("click",function(){
	$("#clientfeesform select[name='client']").val("");
	$("#clientfeesform select[name='city']").val("");
	$("#clientfeesform input[name='deliveredfees']").val("");
	$("#clientfeesform input[name='refusedfees']").val("");
	$("#clientfeesform input[name='returnedfees']").val("");
	$("#clientfeesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-clientfee","click",function(){
	$("#clientfeesform select[name='client']").val($(this).attr("data-client"));
	$("#clientfeesform select[name='city']").val($(this).attr("data-city"));
	$("#clientfeesform input[name='deliveredfees']").val($(this).attr("data-deliveredfees"));
	$("#clientfeesform input[name='refusedfees']").val($(this).attr("data-refusedfees"));
	$("#clientfeesform input[name='returnedfees']").val($(this).attr("data-returnedfees"));
	$("#clientfeesform input[name='id']").val($(this).attr("data-id"));
});

$("#clientfeesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#clientfeesform select[name='client']"));
	isNotEmpty($("#clientfeesform select[name='city']"));
	isNumber($("#clientfeesform input[name='deliveredfees']"));
	if(isNotEmpty($("#clientfeesform select[name='client']"))
	&& isNotEmpty($("#clientfeesform select[name='city']"))
	&& isNumber($("#clientfeesform input[name='deliveredfees']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#clientfeesform input[name='id']").val(),
				client : $("#clientfeesform select[name='client']").val(),
				city : $("#clientfeesform select[name='city']").val(),
				deliveredfees : $("#clientfeesform input[name='deliveredfees']").val(),
				refusedfees : $("#clientfeesform input[name='refusedfees']").val(),
				returnedfees : $("#clientfeesform input[name='returnedfees']").val(),
				action : 'addclientfee'
			},
			success : function(response){
				$("#clientfeesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadClientfees("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#clientfeesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-clientfee","click",function(){
	filterClicked = "yes";
	loadClientfees("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-clientfee","click",function(){
	filterClicked = "yes";
	loadClientfees("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-clientfee","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-reclientfee-clientfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'reclientfeeclientfee'
		},
		success : function(response){
			loadClientfees("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-clientfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteclientfeepermanently'
		},
		success : function(response){
			loadClientfees("0");
		}
	});
});

function loadClientfees(state){
	if($(".lx-table-clientfees .lx-loading").length === 0){
		$(".lx-table-clientfees").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : $("#client").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadclientfees'
		},
		success : function(response){
			$(".lx-table-clientfees .lx-loading").remove();
			$(".lx-table-clientfees").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-gshippingfee").on("click",function(){
	$("#gshippingfeesform select[name='city']").val("");
	$("#gshippingfeesform input[name='deliveredfees']").val("");
	$("#gshippingfeesform input[name='refusedfees']").val("");
	$("#gshippingfeesform input[name='returnedfees']").val("");
	$("#gshippingfeesform input[name='delay']").val("");
	$("#gshippingfeesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-gshippingfee","click",function(){
	$("#gshippingfeesform select[name='city']").val($(this).attr("data-city"));
	$("#gshippingfeesform input[name='deliveredfees']").val($(this).attr("data-deliveredfees"));
	$("#gshippingfeesform input[name='refusedfees']").val($(this).attr("data-refusedfees"));
	$("#gshippingfeesform input[name='returnedfees']").val($(this).attr("data-returnedfees"));
	$("#gshippingfeesform input[name='delay']").val($(this).attr("data-delay"));
	$("#gshippingfeesform input[name='id']").val($(this).attr("data-id"));
});

$("input[name='standardfees']").on("click",function(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			standardfees : ($("input[name='standardfees']").prop("checked") === true?"1":"0"),
			action : 'setstandardfees'
		},
		success : function(response){
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Frais villes standard activés<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);
		}
	});	
});

$("#gshippingfeesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#gshippingfeesform select[name='city']"));
	isNumber($("#gshippingfeesform input[name='deliveredfees']"));
	if(isNotEmpty($("#gshippingfeesform select[name='city']"))
	&& isNumber($("#gshippingfeesform input[name='deliveredfees']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#gshippingfeesform input[name='id']").val(),
				city : $("#gshippingfeesform select[name='city']").val(),
				deliveredfees : $("#gshippingfeesform input[name='deliveredfees']").val(),
				returnedfees : $("#gshippingfeesform input[name='returnedfees']").val(),
				refusedfees : $("#gshippingfeesform input[name='refusedfees']").val(),
				delay : $("#gshippingfeesform input[name='delay']").val(),
				action : 'addgshippingfee'
			},
			success : function(response){
				$("#gshippingfeesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadGShippingfees("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#gshippingfeesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-gshippingfee","click",function(){
	filterClicked = "yes";
	loadGShippingfees("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-gshippingfee","click",function(){
	filterClicked = "yes";
	loadGShippingfees("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-gshippingfee","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-regshippingfee-gshippingfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'regshippingfeegshippingfee'
		},
		success : function(response){
			loadGShippingfees("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-gshippingfee","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletegshippingfeepermanently'
		},
		success : function(response){
			loadGShippingfees("0");
		}
	});
});

function loadGShippingfees(state){
	if($(".lx-table-gshippingfees .lx-loading").length === 0){
		$(".lx-table-gshippingfees").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadgshippingfees'
		},
		success : function(response){
			$(".lx-table-gshippingfees .lx-loading").remove();
			$(".lx-table-gshippingfees").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-shipment").on("click",function(){
	$("#shipmentsform select[name='stock']").val("0");
	$("#shipmentsform input[name='title']").val("");
	$("#shipmentsform input[name='ref']").val("");
	$("#shipmentsform input[name='qty']").val("");
	$("#shipmentsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-shipment","click",function(){
	$("#shipmentsform select[name='stock']").val($(this).attr("data-stock"));
	$("#shipmentsform input[name='title']").val($(this).attr("data-titl"));
	$("#shipmentsform input[name='ref']").val($(this).attr("data-ref"));
	$("#shipmentsform input[name='qty']").val($(this).attr("data-qty"));
	$("#shipmentsform input[name='id']").val($(this).attr("data-id"));
});

$("#shipmentsform select[name='stock']").on("change",function(){
	$("#shipmentsform input[name='title']").val($(this).find("option:selected").attr("data-title"));
	$("#shipmentsform input[name='ref']").val($(this).find("option:selected").attr("data-ref"));
});

$("#shipmentsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#shipmentsform input[name='title']"));
	isNotEmpty($("#shipmentsform input[name='ref']"));
	isNumber($("#shipmentsform input[name='qty']"));
	if(isNotEmpty($("#shipmentsform input[name='title']"))
	&& isNotEmpty($("#shipmentsform input[name='ref']"))
	&& isNumber($("#shipmentsform input[name='qty']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#shipmentsform input[name='id']").val(),
				stock : $("#shipmentsform select[name='stock']").val(),
				title : $("#shipmentsform input[name='title']").val(),
				ref : $("#shipmentsform input[name='ref']").val(),
				qty : $("#shipmentsform input[name='qty']").val(),
				action : 'addshipment'
			},
			success : function(response){
				$("#shipmentsform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadShipments("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Envoie enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#shipmentsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-shipment","click",function(){
	filterClicked = "yes";
	loadShipments("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-shipment","click",function(){
	filterClicked = "yes";
	loadShipments("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-shipment","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-shipment","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoreshipment'
		},
		success : function(response){
			loadShipments("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-shipment","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteshipmentpermanently'
		},
		success : function(response){
			loadShipments("0");
		}
	});
});

function loadShipments(state){
	if($(".lx-table-shipments .lx-loading").length === 0){
		$(".lx-table-shipments").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : $("#client").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadshipments'
		},
		success : function(response){
			$(".lx-table-shipments .lx-loading").remove();
			$(".lx-table-shipments").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-stock").on("click",function(){
	// $("#stocksform select[name='client']").val("");
	$("#stocksform input[name='title']").val("");
	$("#stocksform input[name='ref']").val("");
	$("#stocksform input[name='qty']").val("");
	$("#stocksform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-stock","click",function(){
	$("#stocksform select[name='client']").val($(this).attr("data-client"));
	$("#stocksform input[name='title']").val($(this).attr("data-titl"));
	$("#stocksform input[name='ref']").val($(this).attr("data-ref"));
	$("#stocksform input[name='qty']").val($(this).attr("data-qty"));
	$("#stocksform input[name='id']").val($(this).attr("data-id"));
});

$("#stocksform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#stocksform select[name='client']"));
	isNotEmpty($("#stocksform input[name='title']"));
	isNotEmpty($("#stocksform input[name='ref']"));
	isNumber($("#stocksform input[name='qty']"));
	if(isNotEmpty($("#stocksform select[name='client']"))
	&& isNotEmpty($("#stocksform input[name='title']"))
	&& isNotEmpty($("#stocksform input[name='ref']"))
	&& isNumber($("#stocksform input[name='qty']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#stocksform input[name='id']").val(),
				client : $("#stocksform select[name='client']").val(),
				title : $("#stocksform input[name='title']").val(),
				ref : $("#stocksform input[name='ref']").val(),
				qty : $("#stocksform input[name='qty']").val(),
				action : 'addstock'
			},
			success : function(response){
				$("#stocksform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadStocks("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Envoie enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#stocksform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-stock","click",function(){
	filterClicked = "yes";
	loadStocks("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-stock","click",function(){
	filterClicked = "yes";
	loadStocks("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-stock","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-stock","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorestock'
		},
		success : function(response){
			loadStocks("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-stock","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletestockpermanently'
		},
		success : function(response){
			loadStocks("0");
		}
	});
});

function loadStocks(state){
	if($(".lx-table-stocks .lx-loading").length === 0){
		$(".lx-table-stocks").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : $("#client").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadstocks'
		},
		success : function(response){
			$(".lx-table-stocks .lx-loading").remove();
			$(".lx-table-stocks").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-stockdlm").on("click",function(){
	// $("#stockdlmsform select[name='dlm']").val("");
	$("#stockdlmsform select[name='stock']").val("");
	$("#stockdlmsform input[name='qty']").val("");
	$("#stockdlmsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-stockdlm","click",function(){
	$("#stockdlmsform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#stockdlmsform select[name='stock']").val($(this).attr("data-stock"));
	$("#stockdlmsform input[name='qty']").val($(this).attr("data-qty"));
	$("#stockdlmsform input[name='id']").val($(this).attr("data-id"));
});

$("#stockdlmsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#stockdlmsform select[name='dlm']"));
	isNotEmpty($("#stockdlmsform select[name='stock']"));
	isNumber($("#stockdlmsform input[name='qty']"));
	if(isNotEmpty($("#stockdlmsform select[name='dlm']"))
	&& isNotEmpty($("#stockdlmsform select[name='stock']"))
	&& isNumber($("#stockdlmsform input[name='qty']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#stockdlmsform input[name='id']").val(),
				dlm : $("#stockdlmsform select[name='dlm']").val(),
				stock : $("#stockdlmsform select[name='stock']").val(),
				qty : $("#stockdlmsform input[name='qty']").val(),
				action : 'addstockdlm'
			},
			success : function(response){
				$("#stockdlmsform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadDLMStocks("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Envoie enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#stockdlmsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-stockdlm","click",function(){
	filterClicked = "yes";
	loadDLMStocks("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-stockdlm","click",function(){
	filterClicked = "yes";
	loadDLMStocks("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-stockdlm","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-stockdlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorestockdlm'
		},
		success : function(response){
			loadDLMStocks("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-stockdlm","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletestockdlmpermanently'
		},
		success : function(response){
			loadDLMStocks("0");
		}
	});
});

function loadDLMStocks(state){
	if($(".lx-table-stockdlms .lx-loading").length === 0){
		$(".lx-table-stockdlms").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			dlm : $("#dlm").val(),	
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadstockdlms'
		},
		success : function(response){
			$(".lx-table-stockdlms .lx-loading").remove();
			$(".lx-table-stockdlms").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-packaging").on("click",function(){
	$("#packagingform input[name='title']").val("");
	$("#packagingform input[name='price']").val("");
	$("#packagingform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-packaging","click",function(){
	$("#packagingform input[name='title']").val($(this).attr("data-titl"));
	$("#packagingform input[name='price']").val($(this).attr("data-price"));
	$("#packagingform input[name='id']").val($(this).attr("data-id"));
});

$("#packagingform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#packagingform input[name='title']"));
	isNumber($("#packagingform input[name='price']"));
	if(isNotEmpty($("#packagingform input[name='title']"))
	&& isNumber($("#packagingform input[name='price']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#packagingform input[name='id']").val(),
				title : $("#packagingform input[name='title']").val(),
				price : $("#packagingform input[name='price']").val(),
				action : 'addpackaging'
			},
			success : function(response){
				$("#packagingform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadPackaging("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Utilisateur enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#packagingform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-packaging","click",function(){
	filterClicked = "yes";
	loadPackaging("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-packaging","click",function(){
	filterClicked = "yes";
	loadPackaging("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-packaging","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-repackaging-packaging","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'repackaginghippingfee'
		},
		success : function(response){
			loadPackaging("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-packaging","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletepackagingpermanently'
		},
		success : function(response){
			loadPackaging("0");
		}
	});
});

function loadPackaging(state){
	if($(".lx-table-packaging .lx-loading").length === 0){
		$(".lx-table-packaging").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadpackaging'
		},
		success : function(response){
			$(".lx-table-packaging .lx-loading").remove();
			$(".lx-table-packaging").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}$(".lx-new-shipramassage").on("click",function(){	$("#shipramassagesform input[name='nbcolis']").val("");	$("#shipramassagesform input[name='camp']").val("");	$("#shipramassagesform input[name='ref']").val("");	$("#shipramassagesform textarea[name='note']").val("");	$("#shipramassagesform input[name='id']").val("0");});$("body").delegate(".lx-edit-shipramassage","click",function(){	$("#shipramassagesform input[name='nbcolis']").val($(this).attr("data-nbcolis"));	$("#shipramassagesform input[name='camp']").val($(this).attr("data-camp"));	$("#shipramassagesform input[name='ref']").val($(this).attr("data-ref"));	$("#shipramassagesform textarea[name='note']").val($(this).attr("data-note"));	$("#shipramassagesform input[name='id']").val($(this).attr("data-id"));});$("#shipramassagesform .lx-submit a").on("click",function(){	isNumber($("#shipramassagesform input[name='nbcolis']"));	isNotEmpty($("#shipramassagesform input[name='camp']"));	isNotEmpty($("#shipramassagesform input[name='ref']"));	if(isNumber($("#shipramassagesform input[name='nbcolis']"))		&& isNotEmpty($("#shipramassagesform input[name='camp']"))		&& isNotEmpty($("#shipramassagesform input[name='ref']"))){		if($(this).attr("class") !== "lx-disabled"){			$(this).attr("class","lx-disabled");			if($(this).find("i").length === 0){				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');			}			var ajaxurl = "ajax.php";			$.ajax({				url : ajaxurl,				type : 'post',				data : {					nonce : $("input[name='nonce']").val(),					id : $("#shipramassagesform input[name='id']").val(),					nbcolis : $("#shipramassagesform input[name='nbcolis']").val(),					camp : $("#shipramassagesform input[name='camp']").val(),					ref : $("#shipramassagesform input[name='ref']").val(),					note : $("#shipramassagesform textarea[name='note']").val(),					action : 'addshipramassage'				},				success : function(response){					$("#shipramassagesform .lx-submit a").attr("class","");					$("#shipramassagesform .lx-submit a i").remove();					$(".lx-popup-content > a > .material-icons").trigger("click");					loadShipRamassages("1");					$(".lx-floating-response").remove();					window.clearTimeout(timer);					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Envoie enregistré<i class="material-icons">close</i></p></div>');					$(".lx-floating-response").fadeIn();					timer = window.setTimeout(function(){						$(".lx-floating-response").fadeOut();					},5000);				}			});		}	}	else{		$("#shipramassagesform .lx-submit a i").remove();		$(".lx-floating-response").remove();		window.clearTimeout(timer);		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');		$(".lx-floating-response").fadeIn();		timer = window.setTimeout(function(){			$(".lx-floating-response").fadeOut();		},5000);	}});$("body").delegate(".lx-trash-shipramassage","click",function(){	filterClicked = "yes";	loadShipRamassages("0");	$(".lx-pagination ul").attr("data-state","0");});$("body").delegate(".lx-published-shipramassage","click",function(){	filterClicked = "yes";	loadShipRamassages("1");	$(".lx-pagination ul").attr("data-state","1");});$("body").delegate(".lx-delete-shipramassage","click",function(){	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));});$("body").delegate(".lx-restore-shipramassage","click",function(){	var id = $(this).attr("data-id");	var ajaxurl = "ajax.php";	$.ajax({		url : ajaxurl,		type : 'post',		data : {			id : id,			action : 'restoreshipramassage'		},		success : function(response){			loadShipRamassages("0");		}	});});$("body").delegate(".lx-delete-permanently-shipramassage","click",function(){	var id = $(this).attr("data-id");	var ajaxurl = "ajax.php";	$.ajax({		url : ajaxurl,		type : 'post',		data : {			id : id,			action : 'deleteshipramassagepermanently'		},		success : function(response){			loadShipRamassages("0");		}	});});function loadShipRamassages(state){	if($(".lx-table-shipramassages .lx-loading").length === 0){		$(".lx-table-shipramassages").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');	}	var ajaxurl = "ajax.php";	$.ajax({		url : ajaxurl,		type : 'post',		data : {			state : state,			keyword : $("#keyword").val(),			dlm : ($("#dlm").length)?$("#dlm").val():"",			datestart : $(".lx-keyword #datestart").val(),			dateend : $(".lx-keyword #dateend").val(),					start : $(".lx-pagination ul").attr("data-start"),			nbpage : $(".lx-pagination ul").attr("data-nbpage"),			sortby : $(".lx-keyword input[name='sortby']").val(),			orderby : $(".lx-keyword input[name='orderby']").val(),			action : 'loadshipramassages'		},		success : function(response){			$(".lx-table-shipramassages .lx-loading").remove();			$(".lx-table-shipramassages").html(response);			if(filterClicked === 'yes'){				initPagination();			}			getAllNotifs();		}	});}

$(".lx-add-other-stock").on("click",function(){
	var html = $(this).prev("div").clone();
	$(this).before(html);
	if($(this).prev("div").find(".lx-remove-this-stock").length === 0){
		$(this).prev("div").append('<a href="javascript:;" class="lx-remove-this-stock">Supprimer</a><div class="lx-clear-fix"></div>');
	}
});

$(".lx-popup-content").delegate(".lx-remove-this-stock","click",function(){
	$(this).parent().remove();
});

var htmtproductform = $("#commandsform select[name='product']").clone().html();
$("#commandsform select[name='client']").change(function(){
	$("#commandsform select[name='product']").html(htmtproductform);
	var client = $(this).val();
	if(client !== ""){
		$("#commandsform select[name='product'] option[data-client]").each(function(){
			if($(this).attr("data-client") !== client){
				$(this).remove();
			}
		});
	}
	toDropDown();
});

$(".lx-new-command").on("click",function(){
	// $("#commandsform select[name='client']").val("");
	$("#commandsform select[name='client']").trigger("change");
	$("#commandsform input[name='code']").val("");
	$("#commandsform input[name='fullname']").val("");
	$("#commandsform input[name='phone']").val("");
	$("#commandsform input[name='clientname']").val("");
	$("#commandsform input[name='clientphone']").val("");
	$("#commandsform select[name='dlm']").val("");
	$("#commandsform select[name='dlm']").trigger("change");
	$("#commandsform input[name='city']").val("");
	$("#commandsform select[name='city']").val("");
	$("#commandsform input[name='address']").val("");
	$(".fromstock").css("display","none");
	$("#commandsform select[name='product']").val("0");
	var i = 0;
	$(".lx-add-other-stock").parent().find("> div").each(function(){
	if(i > 0){
			$(this).remove();
		}
		i++;
	});
	$("#commandsform input[name='fromstock']").prop("checked",false);
	$("#commandsform input[name='qty']").val("1");
	$("#commandsform input[name='price']").val("");
	$("#commandsform textarea[name='note']").val("");
	$("#commandsform input[name='change']").prop("checked",false);
	$("#commandsform input[name='openpackage']").prop("checked",true);
	$("#commandsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-command","click",function(){
	$("#commandsform select[name='client']").val($(this).attr("data-client")).trigger("change");
	$("#commandsform input[name='code']").val($(this).attr("data-code"));
	$("#commandsform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#commandsform input[name='phone']").val($(this).attr("data-phone"));
	$("#commandsform input[name='clientname']").val($(this).attr("data-clientname"));
	$("#commandsform input[name='clientphone']").val($(this).attr("data-clientphone"));
	$("#commandsform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#commandsform select[name='dlm']").trigger("change");
	$("#commandsform input[name='city']").val($(this).attr("data-city"));
	$("#commandsform select[name='city']").val($(this).attr("data-city"));
	$("#commandsform input[name='address']").val($(this).attr("data-address"));
	$("#commandsform input[name='fromstock']").prop("checked",$(this).attr("data-fromstock"));
	if($(this).attr("data-fromstock") == true){
		$(".fromstock").css("display","block");
		var stocks = $(this).attr("data-product").split(",");
		var qtys = $(this).attr("data-qty").split(",");
		if(stocks.length > 1){
			var i = 0;
			$(".lx-add-other-stock").parent().find("> div").each(function(){
				if(i > 0){
					$(this).remove();
				}
				i++;
			});
			for(var i=1;i<stocks.length;i++){
				var html = $(".lx-add-other-stock").prev("div").clone();
				$(".lx-add-other-stock").before(html);
				if($(".lx-add-other-stock").prev("div").find(".lx-remove-this-stock").length === 0){
					$(".lx-add-other-stock").prev("div").append('<a href="javascript:;" class="lx-remove-this-stock">Supprimer</a><div class="lx-clear-fix"></div>');
				}
			}
			var k = 0;
			$("#commandsform select[name='product']").each(function(){
				$(this).val(stocks[k]);
				k++;
			});
			k = 0;
			$("#commandsform input[name='qty']").each(function(){
				$(this).val(qtys[k]);
				k++;
			});		
		}
		else{
			$("#commandsform select[name='product']").val($(this).attr("data-product"));
			$("#commandsform input[name='qty']").val($(this).attr("data-qty"));
		}
	}
	else{
		$(".fromstock").css("display","none");
		$("#commandsform input[name='product']").val($(this).attr("data-product"));
		$("#commandsform input[name='qty']").val($(this).attr("data-qty"));
	}
	$("#commandsform input[name='price']").val($(this).attr("data-price"));
	$("#commandsform input[name='extrafees']").val($(this).attr("data-extrafees"));
	$("#commandsform select[name='package']").val($(this).attr("data-package"));
	$("#commandsform textarea[name='note']").val($(this).attr("data-note"));
	$("#commandsform input[name='change']").prop("checked",($(this).attr("data-change")==="1")?true:false);
	$("#commandsform input[name='openpackage']").prop("checked",($(this).attr("data-openpackage")==="1")?true:false);
	$("#commandsform input[name='id']").val($(this).attr("data-id"));
});

$("input[name='fromstock']").on("click",function(){
	if($(this).prop("checked") === true){
		$(".fromstock").css("display","block");
	}
	else{
		$(".fromstock").css("display","none");
	}
});

$("#commandsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	var stocks = "";
	var qtys = "";
	if($("input[name='fromstock']").prop("checked") === true){
		$("#commandsform select[name='product']").each(function(){
			stocks += ","+$(this).val();
		});		
	}
	else{
		stocks += ","+$("#commandsform input[name='product']").val();
	}
	$("#commandsform input[name='qty']").each(function(){
		qtys += ","+$(this).val();
	});
	isNotEmpty($("#commandsform select[name='client']"));
	isNotEmpty($("#commandsform input[name='fullname']"));
	isPhone($("#commandsform input[name='phone']"));
	isNotEmpty($("#commandsform select[name='dlm']"));
	isNotEmpty($("#commandsform select[name='city']"));
	isNumber($("#commandsform input[name='price']"));
	if(isNotEmpty($("#commandsform select[name='client']"))
	&& isNotEmpty($("#commandsform input[name='fullname']"))
	&& isPhone($("#commandsform input[name='phone']"))
	&& isNotEmpty($("#commandsform select[name='dlm']"))
	&& isNotEmpty($("#commandsform select[name='city']"))
	&& isNumber($("#commandsform input[name='price']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#commandsform input[name='id']").val(),
				code : $("#commandsform input[name='code']").val(),
				client : $("#commandsform select[name='client']").val(),
				clientname : $("#commandsform input[name='clientname']").val(),
				clientphone : $("#commandsform input[name='clientphone']").val(),
				fullname : $("#commandsform input[name='fullname']").val(),
				phone : $("#commandsform input[name='phone']").val(),
				dlm : $("#commandsform select[name='dlm']").val(),
				city : $("#commandsform select[name='city']").val(),
				address : $("#commandsform input[name='address']").val(),
				product : stocks,
				qty : qtys,
				price : $("#commandsform input[name='price']").val(),
				extrafees : $("#commandsform input[name='extrafees']").val(),
				package : $("#commandsform select[name='package']").val(),
				note : $("#commandsform textarea[name='note']").val(),
				change : ($("#commandsform input[name='change']").prop("checked") === true)?"1":"0",
				openpackage : ($("#commandsform input[name='openpackage']").prop("checked") === true)?"1":"0",
				state : $("#commandsform input[name='state']").val(),
				phase : $("#commandsform input[name='phase']").val(),
				confirmed : $("#commandsform input[name='confirmed']").val(),
				action : 'addcommand'
			},
			success : function(response){
				console.log(response);
				$("#commandsform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				if($(".lx-pagination ul").attr("data-table") === "ramassage"){
					loadRamassage("1");
				}
				else{
					loadCommands("1");
				}
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Commande enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#commandsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-change-address","click",function(){
	$("#changeaddressform input[name='fullname']").val($(this).attr("data-fullname"));
	$("#changeaddressform input[name='phone']").val($(this).attr("data-phone"));
	$("#changeaddressform input[name='address']").val($(this).attr("data-address"));
	$("#changeaddressform input[name='id']").val($(this).attr("data-id"));
});

$("#changeaddressform .lx-submit a").on("click",function(){
	isNotEmpty($("#changeaddressform input[name='fullname']"));
	isPhone($("#changeaddressform input[name='phone']"));
	if(isNotEmpty($("#changeaddressform input[name='fullname']"))
	&& isPhone($("#changeaddressform input[name='phone']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#changeaddressform input[name='id']").val(),
				fullname : $("#changeaddressform input[name='fullname']").val(),
				phone : $("#changeaddressform input[name='phone']").val(),
				address : $("#changeaddressform input[name='address']").val(),
				action : 'changeaddress'
			},
			success : function(response){
				$("#changeaddressform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadCommands("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Adresse enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#commandsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-command","click",function(){
	filterClicked = "yes";
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage("0");
	}
	else{
		loadCommands("0");
	}
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-command","click",function(){
	filterClicked = "yes";
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage("1");
	}
	else{
		loadCommands("1");
	}
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-command","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-command","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorecommand'
		},
		success : function(response){
			if($(".lx-pagination ul").attr("data-table") === "ramassage"){
				loadRamassage("0");
			}
			else{
				loadCommands("0");
			}
		}
	});
});

$("body").delegate(".lx-delete-permanently-command","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletecommandpermanently'
		},
		success : function(response){
			if($(".lx-pagination ul").attr("data-table") === "ramassage"){
				loadRamassage("0");
			}
			else{
				loadCommands("0");
			}
		}
	});
});

function loadRamassage(state){
	if($(".lx-table-commands .lx-loading").length === 0){
		$(".lx-table-commands").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			worker : $("#worker").val(),
			client : $("#client").val(),
			city : $("#cities").val(),
			product : $("#product").val(),
			statee : ($("#states").length?$("#states").val():""),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			phase : $(".lx-keyword input[name='loadphase']").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadramassage'
		},
		success : function(response){
			$(".lx-table-commands .lx-loading").remove();
			$(".lx-table-commands").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-grab-confirmation").on("click",function(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			action : 'grabcommand'
		},
		success : function(response){
			if(response !== ""){
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+'<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
			else{
				loadRamassage("1");
			}
		}
	});
});

function collectThis(code){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			code : code,
			action : 'collectthis'
		},
		success : function(response){
			if(response === ""){
				loadRamassages($(".lx-pagination ul").attr("data-state"));
				$("#keyword").val(code);
			}
			else{
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+'<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);				
			}
		}
	});
}

$(".lx-new-spreadsheet").on("click",function(){
	$("#spreadsheetsform input[name='title']").val("");
	$("#spreadsheetsform input[name='sheetid']").val("");
	$("#spreadsheetsform input[name='sheetname']").val("");
	$("#spreadsheetsform input[name='lastrow']").val("");
	$("#spreadsheetsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-spreadsheet","click",function(){
	$("#spreadsheetsform select[name='client']").val($(this).attr("data-client"));
	$("#spreadsheetsform input[name='title']").val($(this).attr("data-titl"));
	$("#spreadsheetsform input[name='sheetid']").val($(this).attr("data-sheetid"));
	$("#spreadsheetsform input[name='sheetname']").val($(this).attr("data-sheetname"));
	$("#spreadsheetsform input[name='lastrow']").val($(this).attr("data-lastrow"));
	$("#spreadsheetsform input[name='id']").val($(this).attr("data-id"));
});

$("#spreadsheetsform .lx-submit a").on("click",function(){
	isNotEmpty($("#spreadsheetsform select[name='client']"));
	isNotEmpty($("#spreadsheetsform input[name='title']"));
	isNotEmpty($("#spreadsheetsform input[name='sheetid']"));
	isNotEmpty($("#spreadsheetsform input[name='sheetname']"));
	isNotEmpty($("#spreadsheetsform input[name='lastrow']"));
	if(isNotEmpty($("#spreadsheetsform select[name='client']"))
	&& isNotEmpty($("#spreadsheetsform input[name='title']"))
	&& isNotEmpty($("#spreadsheetsform input[name='sheetid']"))
	&& isNotEmpty($("#spreadsheetsform input[name='sheetname']"))
	&& isNotEmpty($("#spreadsheetsform input[name='lastrow']"))){
		if($(this).attr("class") !== "lx-disabled"){
			$(this).attr("class","lx-disabled");
			if($(this).find("i").length === 0){
				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
			}
			var ajaxurl = "ajax.php";
			$.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					nonce : $("input[name='nonce']").val(),
					id : $("#spreadsheetsform input[name='id']").val(),
					client : $("#spreadsheetsform select[name='client']").val(),
					title : $("#spreadsheetsform input[name='title']").val(),
					sheetid : $("#spreadsheetsform input[name='sheetid']").val(),
					sheetname : $("#spreadsheetsform input[name='sheetname']").val(),
					lastrow : $("#spreadsheetsform input[name='lastrow']").val(),
					action : 'addspreadsheet'
				},
				success : function(response){
					$("#spreadsheetsform .lx-submit a").attr("class","");
					$("#spreadsheetsform .lx-submit a i").remove();
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadSpreadSheets("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Etat enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			});
		}
	}
	else{
		$("#spreadsheetsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-spreadsheets","click",function(){
	filterClicked = "yes";
	loadSpreadSheets("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-spreadsheets","click",function(){
	filterClicked = "yes";
	loadSpreadSheets("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-spreadsheet","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-spreadsheet","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorespreadsheet'
		},
		success : function(response){
			loadSpreadSheets("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-spreadsheet","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletespreadsheetpermanently'
		},
		success : function(response){
			loadSpreadSheets("0");
		}
	});
});

function loadSpreadSheets(state){
	if($(".lx-table-spreadsheets .lx-loading").length === 0){
		$(".lx-table-spreadsheets").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : $("#clients").attr("data-ids"),
			clientaff : $("#clientsaff").attr("data-ids"),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadspreadsheets'
		},
		success : function(response){
			$(".lx-table-spreadsheets .lx-loading").remove();
			$(".lx-table-spreadsheets").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("input[name='searchadvanced']").on("keyup",function(){
	var val = $(this).val();
	var valeur = new RegExp(val,'i');
	$(this).next("ul").find("li").each(function(){
		if(valeur.test($(this).text())){
			$(this).css("display","block");
		}
		else{
			$(this).css("display","none");
		}
	});
});

$(".lx-advanced-select > input").on("click",function(){
	$(".lx-advanced-select div").css({"display":"none"});
	$(this).next("div").css({"display":"block"});
});

$(".lx-advanced-select > div label").on("click",function(){
	var val = "";
	$(this).parents(".lx-advanced-select").find("input[type='checkbox']").not("input[name='selectall']").each(function(){
		if($(this).prop("checked") === true){
			val += "," + $(this).val();
		}
	});
	$(this).parents(".lx-advanced-select").find("> input[type='text']").val(val.substring(1));
});

$(".lx-advanced-select > div label.lx-selectall").on("click",function(){
	var val = "";
	$(this).parents(".lx-advanced-select").find("input[type='checkbox']").not("input[name='selectall']").each(function(){
		$(this).prop("checked",$(".lx-advanced-select > div label.lx-selectall input").prop("checked"));
		if($(this).prop("checked") === true){
			val += "," + $(this).val();
		}
	});
	$(this).parents(".lx-advanced-select").find("> input[type='text']").val(val.substring(1));
});

$(".lx-state-filter").on("click",function(){
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
});

$(".lx-state-empty").on("click",function(){
	$(this).parents(".lx-advanced-select").find("input[type='checkbox']").each(function(){
		$(this).prop("checked",false);
	});
	$(this).parents(".lx-advanced-select").find("> input[type='text']").val("");
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
});

$(".lx-reported-blink").on("click",function(){
	$("#datereport").val($(this).attr("data-datereport") + " - " + $(this).attr("data-datereport"));
	$("#datestartreport").val($(this).attr("data-datereport"));
	$("#dateendreport").val($(this).attr("data-datereport"));
	$("#states").val("Reporté");
	$("input[type='checkbox'][value='Reporté']").prop("checked",true);
	loadCommands($(".lx-pagination ul").attr("data-state"));
});

$(".lx-interested-blink").on("click",function(){
	$("#datereport").val("");
	$("#datestartreport").val("");
	$("#dateendreport").val("");
	$("#states").val("Interessé");
	$("input[type='checkbox'][value='Interessé']").prop("checked",true);
	loadCommands($(".lx-pagination ul").attr("data-state"));
});

$(".lx-changeaddress-blink").on("click",function(){
	$("#datereport").val("");
	$("#datestartreport").val("");
	$("#dateendreport").val("");
	$("#states").val("Changement adresse");
	$("input[type='checkbox'][value='Changement adresse']").prop("checked",true);
	loadCommands($(".lx-pagination ul").attr("data-state"));
});

function loadCommands(state){
	if($(".lx-table-commands .lx-loading").length === 0){
		$(".lx-table-commands").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			codes : $("#codes").val(),
			worker : $("#workers").val(),
			client : $("#clients").val(),
			dlm : $("#dlms").val(),
			sansdlm : $("#sansdlm").val(),
			subdlm : $("#subdlm").val(),
			city : $("#cities").val(),
			product : $("#product").val(),
			statee : $("#states").val(),
			invoiced : $("#invoiced").val(),
			archived : $("#archived").val(),
			treated : $("#treated").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),	
			datestartupdate : $(".lx-keyword #datestartupdate").val(),
			dateendupdate : $(".lx-keyword #dateendupdate").val(),	
			datestartreport : $(".lx-keyword #datestartreport").val(),
			dateendreport : $(".lx-keyword #dateendreport").val(),			
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			type : $(".lx-pagination ul").attr("data-action"),
			action : 'loadcommands'
		},
		success : function(response){
			$(".lx-table-commands .lx-loading").remove();
			$(".lx-table-commands").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

function loadCommandsBS(){
	if($(".lx-table-commands .lx-loading").length === 0){
		$(".lx-table-commands").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			keyword : $("#keyword").val(),
			action : 'loadbs'
		},
		success : function(response){
			$(".lx-table-commands .lx-loading").remove();
			$(".lx-table-commands").html(response);
		}
	});
}

function assignThis(code,dlm){
	if(dlm !== ""){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				code : code,
				dlm : dlm,
				action : 'assigncommand'
			},
			success : function(response){
				$("#keyword").val(code);
				loadCommandsBS();
			}
		});
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);			
	}
}

$("body").delegate(".lx-duplicatecommand","click",function(){
	$(".lx-duplicate-command").attr("data-id",$(this).attr("data-id"));
});

$(".lx-popup-content").delegate(".lx-duplicate-command","click",function(){
	var id = $(this).attr("data-id");
	var phase = $(this).attr("data-phase");
	var state = $(this).attr("data-state");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			phase : phase,
			state : state,
			action : 'duplicatecommand'
		},
		success : function(response){
			$(".lx-popup-content > a > .material-icons").trigger("click");
			loadCommands("1");
		}
	});
});

function returnThis(code){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			code : code,
			action : 'returnthis'
		},
		success : function(response){
			if(response === ""){
				loadCommands("1");
			}
			else{
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+'<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);				
			}
		}
	});
}

function receiveThis(code){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			code : code,
			action : 'receivethis'
		},
		success : function(response){
			if(response === ""){
				loadCommands("1");
			}
			else{
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+'<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);				
			}
		}
	});
}

$("body").delegate(".lx-phone-choice","click",function(){
	$(".lx-phone-layouts a:eq(0)").attr("href","tel:"+$(this).attr("data-phone"));
	var message = "السلام عليكم معكم شركة التوصيل "+$("#appname").val()+" نتواصل معكم من اجل تسليم الطلبية الخاصة بكم ، المرجو التواصل معنا في هذا الرقم او عبر الوتساب. وشكرا.";
	if($("#appname").val() === "Colivraison24"){
		message = "السلام عليكم معكم شركة التوصيل "+$("#appname").val()+" نتواصل معكم من اجل تسليم الطلبية الخاصة بكم ، المرجو إرسال موقعك الجغرافي للتواصل معنا في هذا الرقم او عبر الوتساب. وشكرا.\n\rPrière de partager avec nous votre localisation pour mieux vous service.";
	}
	$(".lx-phone-layouts a:eq(1)").attr("href","sms:"+$(this).attr("data-phone")+"?body="+message);
	$(".lx-phone-layouts a:eq(2)").attr("href","https://wa.me/212"+$(this).attr("data-phone")+"?text="+message);
});

$(".lx-coli-state-delivarymen").on("click",function(){
	$("#editstateform input[name='state']").val($(this).attr("data-state"));
	$(".lx-coli-state-delivarymen i").remove();
	$(this).prepend('<i class="fa fa-check"></i> ');
	if($("#editstateform input[name='state']").val() === "Reporté" || $("#editstateform input[name='state']").val() === "Programmé"){
		$(".datereported").css("display","block");
	}
	else{
		$(".datereported").css("display","none");
		$("#editstateform input[name='datereported']").val("");
	}
});

$("body").delegate(".lx-edit-state","click",function(){
	$("#editstateform input[name='state']").val($(this).attr("data-state"));
	var state = $(this).attr("data-state");
	$(".lx-coli-state-delivarymen").each(function(){
		$(this).find("i").remove();
		$(this).text($(this).text().trim());
		if($(this).text() === state){
			$(this).prepend('<i class="fa fa-check"></i> ');
		}
	});
	$("#editstateform input[name='datereported']").val($(this).attr("data-datereported"));
	if(state === "Reporté" || state === "Programmé"){
		$("#editstateform input[name='datereported']").parent().parent().css("display","block");
	}
	else{
		$("#editstateform input[name='datereported']").parent().parent().css("display","none");
	}
	$("#editstateform textarea[name='comment']").val($(this).attr("data-note"));
	$("#editstateform input[name='id']").val($(this).attr("data-id"));
});

$("#editstateform .lx-submit a").on("click",function(){
	var reporterror = "";
	var cancelerror = "";
	if($("#requirednote").val() === "1"){
		if(($("#editstateform input[name='state']").val() === "Reporté" || $("#editstateform input[name='state']").val() === "Programmé") && $("#editstateform input[name='datereported']").val() === ""){
			reporterror = "yes";
		}
		if(($("#editstateform input[name='state']").val() === "Annulé" || $("#editstateform input[name='state']").val() === "Refusé" || $("#editstateform input[name='state']").val() === "Hors zone") 
			&& $("#editstateform textarea[name='comment']").val() === ""){
			cancelerror = "yes";
		}		
	}
	if(reporterror === "" && cancelerror === ""){
		if($(this).find("i").length === 0){
			$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
		}
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#editstateform input[name='id']").val(),
				state : $("#editstateform input[name='state']").val(),
				datereported : $("#editstateform input[name='datereported']").val(),
				note : $("#editstateform textarea[name='comment']").val(),
				action : 'editstate'
			},
			success : function(response){
				console.log(response);
				$("#editstateform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				if($(".lx-pagination ul").attr("data-table") === "commands"){
					loadCommands($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "ramassage"){
					loadRamassage($(".lx-pagination ul").attr("data-state"));
				}
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Etat enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		if(reporterror === "yes"){
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez une date de reportation !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez ecrire une raison de l\'annulation ou du refus !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}	
	}
});

$(".lx-export-commands").on("click",function(){
	window.location.href = "exportcommands.php?tid="+$("#idticket").val();
});

$("body").delegate(".lx-show-history","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'showcommandhistory'
		},
		success : function(response){
			$(".commandhistory .lx-add-form").html(response);
			
		}
	});	
});

$("body").delegate(".lx-print-ticket","click",function(){
	$("#idticket").val($(this).attr("data-id"));
});

$("body").delegate("input[type='checkbox'][name='command']","click",function(){
	var ids = "0";
	$("input[type='checkbox'][name='command']").each(function(){
		if($(this).prop("checked") === true){
			ids += ","+$(this).val();
		}
	});
	$("#idticket").val(ids);
});

$(".lx-print-tickets").on("click",function(){
	if($(this).attr("data-model") === "5"){
		window.location.href = "printticketszebra.php?tid="+$("#idticket").val();
	}
	else{
		window.location.href = "printclienttickets.php?tid="+$("#idticket").val()+"&model="+$(this).attr("data-model");
	}
});

$(".lx-print-bl-client").on("click",function(){
	if($("#idticket").val() !== "" && $("#client2").val() !== "0"){
		window.location.href = "printblclient.php?tid="+$("#idticket").val()+"&client="+$("#client2").val();
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez choisir des commandes pour créer un BL !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#blform .lx-submit a").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
	if($("#idticket").val() !== ""){
		var dlm = $("#blform select[name='dlm']").val();
		if(dlm !== ""){
			window.location.href = "printblmoderator.php?tid="+$("#idticket").val()+"&dlm="+dlm;
			$("#blform select[name='dlm']").val("");
			$(".lx-popup-inside").trigger("click");
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez des commandes pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#beform .lx-submit a").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
	if($("#idticket").val() !== ""){
		var dlm = $("#beform select[name='dlm']").val();
		if(dlm !== ""){
			window.location.href = "printshipment.php?tid="+$("#idticket").val()+"&dlm="+dlm;
			$("#beform select[name='dlm']").val("");
			$(".lx-popup-inside").trigger("click");
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez des commandes pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#bslform .lx-submit a").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
	if($("#idticket").val() !== ""){
		var subdlm = $("#bslform select[name='subdlm']").val();
		if(subdlm !== ""){
			window.location.href = "printbslmoderator.php?tid="+$("#idticket").val()+"&subdlm="+subdlm;
			$("#bslform select[name='subdlm']").val("");
			$(".lx-popup-inside").trigger("click");
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez des commandes pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#brform .lx-submit a").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
	if($("#idticket").val() !== ""){
		var client = $("#brform select[name='client']").val();
		if(client !== ""){
			window.location.href = "printbrmoderator.php?tid="+$("#idticket").val()+"&client="+client;
			$("#brform select[name='client']").val("");
			$(".lx-popup-inside").trigger("click");
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un client !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez des commandes pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("#brlform .lx-submit a").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
	if($("#idticket").val() !== ""){
		var dlm = $("#brlform select[name='dlm']").val();
		if(dlm !== ""){
			window.location.href = "printbrlmoderator.php?tid="+$("#idticket").val()+"&dlm="+dlm;
			$("#brlform select[name='dlm']").val("");
			$(".lx-popup-inside").trigger("click");
		}
		else{
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur !!<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);			
		}
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez des commandes pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-delete-bl","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

function loadBLS(state){
	if($(".lx-table-bls .lx-loading").length === 0){
		$(".lx-table-bls").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			dlm : ($("#dlm").length)?$("#dlm").val():"",
			subdlm : ($("#subdlm").length)?$("#subdlm").val():"",
			client : ($("#client").length)?$("#client").val():"",
			type : $("#type").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadbls'
		},
		success : function(response){
			$(".lx-table-bls .lx-loading").remove();
			$(".lx-table-bls").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

function addToBR(code,client){
	if(client !== ""){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				code : code,
				client : client,
				action : 'addtobr'
			},
			success : function(response){
				console.log(response);
				if(response !== ""){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+' !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);					
				}
				else{
					loadBLS("1");
				}
			}
		});
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un client !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
}

function addToBL(code,dlm){
	if(dlm !== ""){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				code : code,
				dlm : dlm,
				action : 'addtobl'
			},
			success : function(response){
				console.log(response);
				if(response !== ""){
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+' !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);					
				}
				else{
					loadBLS("1");
				}
			}
		});
	}
	else{
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez un livreur !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
}

$(".lx-new-facture").on("click",function(){
	$("#facturesform select[name='client']").val("");
	$("#facturesform select[name='dlm']").val("");
	$("#facturesform input[name='commands']").val("");
	$(".lx-commands-added ins").text("0");
	$("#facturesform input[name='id']").val("0");
	loadFactureColis($(".lx-pagination ul").attr("data-type"),"loadfacturecommandstoadd");
});

$("body").delegate(".lx-edit-facture","click",function(){
	$("#facturesform select[name='client']").val($(this).attr("data-client"));
	$("#facturesform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#facturesform input[name='commands']").val($(this).attr("data-commands"));
	$(".lx-commands-added ins").text($(this).attr("data-nbcommands"));
	$("#facturesform input[name='id']").val($(this).attr("data-id"));
	$(".lx-commands-added").trigger("click");
});

$(".lx-commands-tabs a").on("click",function(){
	$(".lx-commands-tabs a").removeClass("active");
	$(this).addClass("active");
});

$(".lx-commands-to-add").on("click",function(){
	loadFactureColis($(".lx-pagination ul").attr("data-type"),"loadfacturecommandstoadd");
});

$(".lx-commands-added").on("click",function(){
	loadFactureColis($(".lx-pagination ul").attr("data-type"),"loadfacturecommandsadded");
});

$(".lx-popup-content").delegate(".lx-commands-to-remove","click",function(){
	if($("#facturesform").length){
		$(this).parent().parent().fadeOut();
		var commands = $("#facturesform input[name='commands']").val().split(",");
		var index = commands.indexOf($(this).attr("data-id"));
		if(index > -1){
			commands.splice(index,1);
		}
		$("#facturesform input[name='commands']").val(commands.join(","));		
	}
});

function loadFactureColis(type,action){
	if($(".lx-table-factures-commands .lx-loading").length === 0){
		$(".lx-table-factures-commands").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			type : type,
			id : $("#facturesform input[name='id']").val(),
			commands : $("#facturesform input[name='commands']").val(),
			client : ($("#client2").length)?$("#client2").val():"",
			dlm : ($("#dlm2").length)?$("#dlm2").val():"",
			action : action
		},
		success : function(response){
			$(".lx-table-factures-commands .lx-loading").remove();
			$(".lx-table-factures-commands").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-popup-content").delegate(".lx-commands-to-remove","click",function(){
	if($("#facturesform").length){
		$(this).parent().parent().fadeOut();
		var commands = $("#facturesform input[name='commands']").val().split(",");
		var index = commands.indexOf($(this).attr("data-id"));
		if(index > -1){
			commands.splice(index,1);
		}
		$("#facturesform input[name='commands']").val(commands.join(","));		
	}
});

$("#facturesform .lx-submit a").on("click",function(){
	var ids = "";
	$(".lx-popup .lx-table input[type='checkbox']:checked").each(function(){
		if(Number.isInteger(parseInt($(this).val()))){
			ids += ","+$(this).val();
		}
	});
	if($("input[name='commands']").val() === ""){
		$("input[name='commands']").val(ids.substring(1));
	}
	else{
		$("input[name='commands']").val($("input[name='commands']").val()+ids);
	}
	if($("#facturesform input[name='commands']").val() !== "" || $("#facturesform input[name='id']").val() !== "0"){
			if($(this).find("i").length === 0){
				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
			}
			var user = "";
			if($(".lx-pagination ul").attr("data-type") === "client"){
				user = $("#facturesform select[name='client']").val();
			}
			if($(".lx-pagination ul").attr("data-type") === "dlm"){
				user = $("#facturesform select[name='dlm']").val();
			}
			var ajaxurl = "ajax.php";
			$.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					id : $("#facturesform input[name='id']").val(),
					user : user,
					type : $(".lx-pagination ul").attr("data-type"),
					commands : $("#facturesform input[name='commands']").val(),
					action : 'addfacture'
				},
				success : function(response){
					console.log(response);
					$("#facturesform .lx-submit a i").remove();
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadFactures($(".lx-pagination ul").attr("data-state"));
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Facture enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			});
	}
	else{
		$("#facturesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez choisir les commands pour les ajouter au bon !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-edit-note-facture","click",function(){
	$("#notefactureform input[name='price']").val($(this).attr("data-price"));
	$("#notefactureform input[name='nbcommands']").val($(this).attr("data-nbcommands"));
	$("#notefactureform input[name='charges']").val($(this).attr("data-charges"));
	$("#notefactureform textarea[name='note']").val($(this).attr("data-note"));
	$("#notefactureform input[name='id']").val($(this).attr("data-id"));
});

$("#notefactureform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : $("#notefactureform input[name='id']").val(),
			price : $("#notefactureform input[name='price']").val(),
			nbcommands : $("#notefactureform input[name='nbcommands']").val(),
			charges : $("#notefactureform input[name='charges']").val(),
			note : $("#notefactureform textarea[name='note']").val(),
			action : 'editnotefacture'
		},
		success : function(response){
			$("#notefactureform .lx-submit a i").remove();
			$(".lx-popup-content > a > .material-icons").trigger("click");
			loadFactures("1");
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Etat enregistré<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);
		}
	});
});

$("body").delegate(".lx-delete-facture","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

function loadFactures(state){
	if($(".lx-table-factures .lx-loading").length === 0){
		$(".lx-table-factures").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : ($("#client").length?$("#client").val():""),
			dlm : ($("#dlm").length?$("#dlm").val():""),
			validated : $("#validated").val(),
			received : $("#received").val(),
			type : $("#type").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadfactures'
		},
		success : function(response){
			$(".lx-table-factures .lx-loading").remove();
			$(".lx-table-factures").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-merge-fct").on("click",function(){
	var nbcommands = 0;
	var price = 0;
	var codes = "0";
	var html = '<table cellpadding="0" cellspacing="0">';
	html += '<tr>';
	html += '<td>Code</td>';
	html += '<td>Nb Commands</td>';
	html += '<td>Montant</td>';
	html += '</tr>';
	$(".lx-main .lx-table input[name='facture']:checked").each(function(){
		codes += ","+$(this).attr("data-code");
		nbcommands += parseInt($(this).attr("data-nbcommands"));
		price += parseInt($(this).attr("data-price"));
		html += '<tr>';
		html += '<td style="white-space:nowrap;">'+$(this).attr("data-code")+'</td>';
		html += '<td>'+$(this).attr("data-nbcommands")+'</td>';
		html += '<td>'+$(this).attr("data-price")+'DH</td>';
		html += '</tr>';		
	});
	html += '<tr>';
	html += '<td><b>Total</b></td>';
	html += '<td><b>'+nbcommands+'</b></td>';
	html += '<td><b>'+price+'DH</b></td>';
	html += '</tr>';
	html += '</table>';
	$("#mergefctform input[name='ids']").val(codes);
	$("#mergefctform input[name='nbcommands']").val(nbcommands);
	$("#mergefctform input[name='price']").val(price);
	$(".lx-table-merged-fct").html(html);
});

$("#mergefctform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			codes : $("#mergefctform input[name='ids']").val(),
			nbcommands : $("#mergefctform input[name='nbcommands']").val(),
			price : $("#mergefctform input[name='price']").val(),
			action : 'mergefct'
		},
		success : function(response){
			console.log(response);
			$("#mergefctform .lx-submit a i").remove();
			$(".lx-popup-content > a > .material-icons").trigger("click");
			loadFactures("1");
			$(".lx-floating-response").remove();
			window.clearTimeout(timer);
			$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Bien enregistré<i class="material-icons">close</i></p></div>');
			$(".lx-floating-response").fadeIn();
			timer = window.setTimeout(function(){
				$(".lx-floating-response").fadeOut();
			},5000);
		}
	});
});

$(".lx-new-expense").on("click",function(){
	$("#expensesform input[name='cost']").val("");
	$("#expensesform select[name='dlm']").val("");
	$("#expensesform textarea[name='description']").val("");
	$("#expensesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-expense","click",function(){
	$("#expensesform input[name='cost']").val($(this).attr("data-cost"));
	$("#expensesform select[name='dlm']").val($(this).attr("data-dlm"));
	$("#expensesform textarea[name='description']").val($(this).attr("data-description"));
	$("#expensesform input[name='id']").val($(this).attr("data-id"));
});

$("#expensesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNumber($("#expensesform input[name='cost']"));
	isNotEmpty($("#expensesform textarea[name='description']"));
	if(isNumber($("#expensesform input[name='cost']"))
	&& isNotEmpty($("#expensesform textarea[name='description']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#expensesform input[name='id']").val(),
				cost : $("#expensesform input[name='cost']").val(),
				dlm : $("#expensesform select[name='dlm']").val(),
				description : $("#expensesform textarea[name='description']").val(),
				action : 'addexpense'
			},
			success : function(response){
				$("#expensesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadExpenses("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Dépence enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#expensesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-expense","click",function(){
	filterClicked = "yes";
	loadExpenses("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-expense","click",function(){
	filterClicked = "yes";
	loadExpenses("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-expense","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-expense","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoreexpense'
		},
		success : function(response){
			loadExpenses("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-expense","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deleteexpensepermanently'
		},
		success : function(response){
			loadExpenses("0");
		}
	});
});

function loadExpenses(state){
	if($(".lx-table-expenses .lx-loading").length === 0){
		$(".lx-table-expenses").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			dlm : $("#dlm").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadexpenses'
		},
		success : function(response){
			$(".lx-table-expenses .lx-loading").remove();
			$(".lx-table-expenses").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-reclamation").on("click",function(){
	$("#reclamationsform input[name='subject']").val("");
	$("#reclamationsform select[name='service']").val("");
	$("#reclamationsform input[name='code']").val("");
	$("#reclamationsform select[name='city']").val("");
	$("#reclamationsform textarea[name='message']").val("");
	$("#reclamationsform input[name='id']").val("0");
});

$("#reclamationsform .lx-submit a").on("click",function(){
	isNotEmpty($("#reclamationsform textarea[name='message']"));
	if(isNotEmpty($("#reclamationsform textarea[name='message']"))){
		if($(this).attr("class") !== "lx-disabled"){
			$(this).attr("class","lx-disabled");
			if($(this).find("i").length === 0){
				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
			}
			var ajaxurl = "ajax.php";
			$.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					nonce : $("input[name='nonce']").val(),
					id : $("#reclamationsform input[name='id']").val(),
					subject : $("#reclamationsform input[name='subject']").val(),
					service : $("#reclamationsform select[name='service']").val(),
					code : $("#reclamationsform input[name='code']").val(),
					city : $("#reclamationsform select[name='city']").val(),
					message : $("#reclamationsform textarea[name='message']").val(),
					action : 'addreclamation'
				},
				success : function(response){
					$("#reclamationsform .lx-submit a").attr("class","");
					$("#reclamationsform .lx-submit a i").remove();
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadReclamations("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Réclamation enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			});
		}
	}
	else{
		$("#reclamationsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-add-reply","click",function(){
	$(".lx-message-reclamation").html($(this).attr("data-message"));
	$("#replyform textarea[name='message']").val("");
	$("#replyform input[name='id']").val($(this).attr("data-id"));
});

$("#replyform .lx-submit a").on("click",function(){
	isNotEmpty($("#replyform textarea[name='message']"));
	if(isNotEmpty($("#replyform textarea[name='message']"))){
		if($(this).attr("class") !== "lx-disabled"){
			$(this).attr("class","lx-disabled");
			if($(this).find("i").length === 0){
				$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
			}
			var ajaxurl = "ajax.php";
			$.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					nonce : $("input[name='nonce']").val(),
					id : $("#replyform input[name='id']").val(),
					message : $("#replyform textarea[name='message']").val(),
					action : 'addreply'
				},
				success : function(response){
					$("#replyform .lx-submit a").attr("class","");
					$("#replyform .lx-submit a i").remove();
					$(".lx-popup-content > a > .material-icons").trigger("click");
					loadReclamations("1");
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Réponse enregistré<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);
				}
			});
		}
	}
	else{
		$("#replyform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-reclamation","click",function(){
	filterClicked = "yes";
	loadReclamations("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-reclamation","click",function(){
	filterClicked = "yes";
	loadReclamations("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-reclamation","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-reclamation","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restorereclamation'
		},
		success : function(response){
			loadReclamations("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-reclamation","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletereclamationpermanently'
		},
		success : function(response){
			loadReclamations("0");
		}
	});
});

function loadReclamations(state){
	if($(".lx-table-reclamations .lx-loading").length === 0){
		$(".lx-table-reclamations").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			client : ($("#client").length)?$("#client").val():"",
			service : $("#service").val(),
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),		
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadreclamations'
		},
		success : function(response){
			$(".lx-table-reclamations .lx-loading").remove();
			$(".lx-table-reclamations").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-smsdevice").on("click",function(){
	$("#smsdevicesform input[name='device']").val("");
	$("#smsdevicesform input[name='apikey']").val("");
	$("#smsdevicesform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-smsdevice","click",function(){
	$("#smsdevicesform input[name='device']").val($(this).attr("data-device"));
	$("#smsdevicesform input[name='apikey']").val($(this).attr("data-apikey"));
	$("#smsdevicesform input[name='id']").val($(this).attr("data-id"));
});

$("#smsdevicesform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#smsdevicesform input[name='device']"));
	isNotEmpty($("#smsdevicesform input[name='apikey']"));
	if(isNotEmpty($("#smsdevicesform input[name='device']"))
	&& isNotEmpty($("#smsdevicesform input[name='apikey']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#smsdevicesform input[name='id']").val(),
				device : $("#smsdevicesform input[name='device']").val(),
				apikey : $("#smsdevicesform input[name='apikey']").val(),
				action : 'addsmsdevice'
			},
			success : function(response){
				$("#smsdevicesform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadSMSDevices("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Appareil enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#smsdevicesform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-smsdevice","click",function(){
	filterClicked = "yes";
	loadSMSDevices("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-smsdevice","click",function(){
	filterClicked = "yes";
	loadSMSDevices("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-smsdevice","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-smsdevice","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoresmsdevice'
		},
		success : function(response){
			loadSMSDevices("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-smsdevice","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletesmsdevicepermanently'
		},
		success : function(response){
			loadSMSDevices("0");
		}
	});
});

function loadSMSDevices(state){
	if($(".lx-table-smsdevices .lx-loading").length === 0){
		$(".lx-table-smsdevices").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadsmsdevices'
		},
		success : function(response){
			$(".lx-table-smsdevices .lx-loading").remove();
			$(".lx-table-smsdevices").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$(".lx-new-smsmodel").on("click",function(){
	$("#smsmodelsform input[name='title']").val("");
	$("#smsmodelsform textarea[name='message']").val("");
	$("#smsmodelsform input[name='id']").val("0");
});

$("body").delegate(".lx-edit-smsmodel","click",function(){
	$("#smsmodelsform input[name='title']").val($(this).attr("data-titl"));
	$("#smsmodelsform textarea[name='message']").val($(this).attr("data-message"));
	$("#smsmodelsform input[name='id']").val($(this).attr("data-id"));
});

$("#smsmodelsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#smsmodelsform input[name='title']"));
	isNotEmpty($("#smsmodelsform textarea[name='message']"));
	if(isNotEmpty($("#smsmodelsform input[name='title']"))
	&& isNotEmpty($("#smsmodelsform textarea[name='message']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				id : $("#smsmodelsform input[name='id']").val(),
				title : $("#smsmodelsform input[name='title']").val(),
				message : $("#smsmodelsform textarea[name='message']").val(),
				action : 'addsmsmodel'
			},
			success : function(response){
				$("#smsmodelsform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadSMSModels("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Modéle enregistré<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#smsmodelsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

$("body").delegate(".lx-trash-smsmodel","click",function(){
	filterClicked = "yes";
	loadSMSModels("0");
	$(".lx-pagination ul").attr("data-state","0");
});

$("body").delegate(".lx-published-smsmodel","click",function(){
	filterClicked = "yes";
	loadSMSModels("1");
	$(".lx-pagination ul").attr("data-state","1");
});

$("body").delegate(".lx-delete-smsmodel","click",function(){
	$(".lx-delete-record").attr("data-id",$(this).attr("data-id"));
});

$("body").delegate(".lx-restore-smsmodel","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'restoresmsmodel'
		},
		success : function(response){
			loadSMSModels("0");
		}
	});
});

$("body").delegate(".lx-delete-permanently-smsmodel","click",function(){
	var id = $(this).attr("data-id");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'deletesmsmodelpermanently'
		},
		success : function(response){
			loadSMSModels("0");
		}
	});
});

function loadSMSModels(state){
	if($(".lx-table-smsmodels .lx-loading").length === 0){
		$(".lx-table-smsmodels").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			state : state,
			keyword : $("#keyword").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadsmsmodels'
		},
		success : function(response){
			$(".lx-table-smsmodels .lx-loading").remove();
			$(".lx-table-smsmodels").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

$("body").delegate(".lx-open-sms-model","click",function(){
	$("#sendsmsform input[name='id']").val($(this).attr("data-id"));
	$("#idticket").val($(this).attr("data-id"));
});

$("#sendsmsform select[name='sms']").on("change",function(){
	var id = $(this).val();
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : 'loadsms'
		},
		success : function(response){
			$("#sendsmsform textarea[name='message']").val(response);
		}
	});	
});

$("#sendsmsform .lx-submit a").on("click",function(){
	if($(this).find("i").length === 0){
		$(this).prepend('<i class="fa fa-circle-notch fa-spin"></i> ');
	}
	isNotEmpty($("#sendsmsform textarea[name='message']"));
	if(isNotEmpty($("#smsmodelsform textarea[name='message']"))){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				ids : $("#idticket").val(),
				sms : $("#sendsmsform select[name='sms']").val(),
				action : 'sendsms'
			},
			success : function(response){
				console.log(response);
				$("#sendsmsform .lx-submit a i").remove();
				$(".lx-popup-content > a > .material-icons").trigger("click");
				loadSMSModels("1");
				$(".lx-floating-response").remove();
				window.clearTimeout(timer);
				$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Message envoyé<i class="material-icons">close</i></p></div>');
				$(".lx-floating-response").fadeIn();
				timer = window.setTimeout(function(){
					$(".lx-floating-response").fadeOut();
				},5000);
			}
		});
	}
	else{
		$("#sendsmsform .lx-submit a i").remove();
		$(".lx-floating-response").remove();
		window.clearTimeout(timer);
		$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Veuillez remplir les champs en rouge !!<i class="material-icons">close</i></p></div>');
		$(".lx-floating-response").fadeIn();
		timer = window.setTimeout(function(){
			$(".lx-floating-response").fadeOut();
		},5000);
	}
});

function loadSalaries(){
	if($(".lx-table-salaries .lx-loading").length === 0){
		$(".lx-table-salaries").prepend('<div class="lx-loading" style="padding:10px;text-align:center;"><p>Please wait ...<br /><i class="fa fa-circle-notch fa-spin"></i></p></div>');
	}
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			datestart : $(".lx-keyword #datestart").val(),
			dateend : $(".lx-keyword #dateend").val(),
			start : $(".lx-pagination ul").attr("data-start"),
			nbpage : $(".lx-pagination ul").attr("data-nbpage"),
			sortby : $(".lx-keyword input[name='sortby']").val(),
			orderby : $(".lx-keyword input[name='orderby']").val(),
			action : 'loadsalaries'
		},
		success : function(response){
			$(".lx-table-salaries .lx-loading").remove();
			$(".lx-table-salaries").html(response);
			if(filterClicked === 'yes'){
				initPagination();
			}
		}
	});
}

function loadLog(){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			datelog : $("#datelog").val(),
			action : 'loadlog'
		},
		success : function(response){
			$("#loghistory").html(response);	
		}
	});
}

$("body").delegate(".lx-on-off","click",function(){
	if($(this).attr("data-state") !== "off"){
		$(this).removeClass("lx-on-off-blue");
		$(this).attr("data-state","off");
		changeState($(this).attr("data-table"),$(this).attr("data-column"),$(this).attr("data-id"),"off");
	}
	else{
		$(this).addClass("lx-on-off-blue");
		$(this).attr("data-state","on");
		changeState($(this).attr("data-table"),$(this).attr("data-column"),$(this).attr("data-id"),"on");
	}
});

function changeState(table,column,id,state){
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			table : table,
			column : column,
			id : id,
			state : state,
			action : 'changestate'
		},
		success : function(response){
			console.log(response);
		}
	});
}

$(".lx-cancel-delete").on("click",function(){
	$(".lx-popup-content > a > .material-icons").trigger("click");
});

$(".lx-delete-record").on("click",function(){
	var id = $(this).attr("data-id");
	var action = $(this).attr("data-action");
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			id : id,
			action : action
		},
		success : function(response){
			$(".lx-popup-content > a > .material-icons").trigger("click");
			if($(".lx-pagination ul").attr("data-table") === "users"){
				loadUsers($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "cities"){
				loadCities($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "dlm"){
				loadDLM($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "subdlm"){
				loadSubDLM($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
				loadShippingfees($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
				loadGShippingfees($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "trackingstates"){				loadTrackingstates($(".lx-pagination ul").attr("data-state"));			}						if($(".lx-pagination ul").attr("data-table") === "shipramassages"){				loadShipRamassages($(".lx-pagination ul").attr("data-state"));			}
			if($(".lx-pagination ul").attr("data-table") === "clients"){
				loadClients($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "workers"){
				loadWorkers($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "clientfees"){
				loadClientfees($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "shipments"){
				loadShipments($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "stocks"){
				loadStocks($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
				loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "ramassage"){
				loadRamassage($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "commands"){
				loadCommands($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "bls"){
				loadBLS($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "factures"){
				loadFactures($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "notices"){
				loadNotices($(".lx-pagination ul").attr("data-state"));
			}	
			if($(".lx-pagination ul").attr("data-table") === "expenses"){
				loadExpenses($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "reclamations"){
				loadReclamations($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
				loadSMSDevices($(".lx-pagination ul").attr("data-state"));
			}
			if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
				loadSMSModels($(".lx-pagination ul").attr("data-state"));
			}	
		}
	});
});

$(".lx-search-keyword").on("click",function(){
	filterClicked = "yes";
	if($(".lx-pagination ul").attr("data-table") === "users"){
		loadUsers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "cities"){
		loadCities($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "dlm"){
		loadDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "subdlm"){
		loadSubDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
		loadShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
		loadGShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "trackingstates"){		loadTrackingstates($(".lx-pagination ul").attr("data-state"));	}		if($(".lx-pagination ul").attr("data-table") === "shipramassages"){		loadShipRamassages($(".lx-pagination ul").attr("data-state"));	}
	if($(".lx-pagination ul").attr("data-table") === "clients"){
		loadClients($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "workers"){
		loadWorkers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "clientfees"){
		loadClientfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shipments"){
		loadShipments($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "stocks"){
		loadStocks($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
		loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "bls"){
		loadBLS($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "factures"){
		loadFactures($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "notices"){
		loadNotices($(".lx-pagination ul").attr("data-state"));
	}	
	if($(".lx-pagination ul").attr("data-table") === "expenses"){
		loadExpenses($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "reclamations"){
		loadReclamations($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
		loadSMSDevices($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
		loadSMSModels($(".lx-pagination ul").attr("data-state"));
	}	
});

$("#client2,#dlm2").on("change",function(){
	filterClicked = "yes";
	loadFactureColis($(".lx-pagination ul").attr("data-type"),"loadfacturecommandstoadd");
});

$("#client,#worker,#dlm,#subdlm,#city,#product,#invoiced,#validated,#received,#type").on("change",function(){
	filterClicked = "yes";
	if($("#salescontainer").length){
		loadChartData();
	}
	if($(".lx-pagination ul").attr("data-table") === "subdlm"){
		loadSubDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
		loadShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
		loadGShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "clientfees"){
		loadClientfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shipments"){
		loadShipments($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "stocks"){
		loadStocks($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
		loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "factures"){
		loadFactures($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "notices"){
		loadNotices($(".lx-pagination ul").attr("data-state"));
	}	
	if($(".lx-pagination ul").attr("data-table") === "expenses"){
		loadExpenses($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "reclamations"){
		loadReclamations($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
		loadSMSDevices($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
		loadSMSModels($(".lx-pagination ul").attr("data-state"));
	}	
});

function initPagination(){
	filterClicked = 'no';
	if(parseInt($("#posts").val()) > parseInt($(".lx-pagination ul").attr("data-nbpage"))){
		$(".lx-pagination").css("display","block");
		$(".lx-pagination ul").attr("data-start",0);
		$(".lx-pagination ul").attr("data-posts",$("#posts").val());
		$(".lx-pagination ul li ins").text("1");
		$(".lx-pagination ul li abbr").text(Math.ceil(parseInt($("#posts").val()) / parseInt($(".lx-pagination ul").attr("data-nbpage"))));
		$(".lx-pagination ul li .next").removeClass("disabled");
		$(".lx-pagination ul li .previous").removeClass("disabled");
		$(".lx-pagination ul li .previous").addClass("disabled");
	}
	else{
		$(".lx-pagination").css("display","none");
	}
}

$(".lx-pagination ul li .next").on("click",function(){
	filterClicked = 'no';
	var start = parseInt($(".lx-pagination ul").attr("data-start"));
	var nbpage = parseInt($(".lx-pagination ul").attr("data-nbpage"));
	var posts = parseInt($(".lx-pagination ul").attr("data-posts"));
	if((start + nbpage) < posts){
		if((start + (nbpage * 2)) >= posts){
			$(this).addClass("disabled");
		}
		$(".lx-pagination ul li .previous").removeClass("disabled");
		$(".lx-pagination ul").attr("data-start",(start+nbpage));
		$(".lx-pagination ul li span ins").text((start+(nbpage*2))/nbpage);
		$(".lx-pagination ul li #pgbumber").val(parseInt($(".lx-pagination ul li span ins").text()) - 1);
		if($(".lx-pagination ul").attr("data-table") === "users"){
			loadUsers($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "cities"){
			loadCities($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "dlm"){
			loadDLM($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "subdlm"){
			loadSubDLM($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
			loadShippingfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
			loadGShippingfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "trackingstates"){			loadTrackingstates($(".lx-pagination ul").attr("data-state"));		}				if($(".lx-pagination ul").attr("data-table") === "shipramassages"){			loadShipRamassages($(".lx-pagination ul").attr("data-state"));		}
		if($(".lx-pagination ul").attr("data-table") === "clients"){
			loadClients($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "workers"){
			loadWorkers($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "clientfees"){
			loadClientfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "shipments"){
			loadShipments($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "stocks"){
			loadStocks($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
			loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "ramassage"){
			loadRamassage($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "commands"){
			loadCommands($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "bls"){
			loadBLS($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "factures"){
			loadFactures($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "notices"){
			loadNotices($(".lx-pagination ul").attr("data-state"));
		}	
		if($(".lx-pagination ul").attr("data-table") === "expenses"){
			loadExpenses($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "reclamations"){
			loadReclamations($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
			loadSMSDevices($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
			loadSMSModels($(".lx-pagination ul").attr("data-state"));
		}		
		$('html, body').animate({
			scrollTop: $(".lx-main-content").offset().top
		}, 1000);
	}
});

$(".lx-pagination ul li .previous").on("click",function(){
	filterClicked = 'no';
	var start = parseInt($(".lx-pagination ul").attr("data-start"));
	var nbpage = parseInt($(".lx-pagination ul").attr("data-nbpage"));
	var posts = parseInt($(".lx-pagination ul").attr("data-posts"));
	if(start > 0){
		if((start - nbpage) === 0){
			$(this).addClass("disabled");
		}
		$(".lx-pagination ul li .next").removeClass("disabled");
		$(".lx-pagination ul").attr("data-start",(start-nbpage));
		$(".lx-pagination ul li span ins").text(start/nbpage);
		$(".lx-pagination ul li #pgbumber").val(parseInt($(".lx-pagination ul li span ins").text()) - 1);
		if($(".lx-pagination ul").attr("data-table") === "users"){
			loadUsers($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "cities"){
			loadCities($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "dlm"){
			loadDLM($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "subdlm"){
			loadSubDLM($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
			loadShippingfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
			loadGShippingfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "trackingstates"){			loadTrackingstates($(".lx-pagination ul").attr("data-state"));		}				if($(".lx-pagination ul").attr("data-table") === "shipramassages"){			loadShipRamassages($(".lx-pagination ul").attr("data-state"));		}
		if($(".lx-pagination ul").attr("data-table") === "workers"){
			loadWorkers($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "clientfees"){
			loadClientfees($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "shipments"){
			loadShipments($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "stocks"){
			loadStocks($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
			loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "ramassage"){
			loadRamassage($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "commands"){
			loadCommands($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "bls"){
			loadBLS($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "factures"){
			loadFactures($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "notices"){
			loadNotices($(".lx-pagination ul").attr("data-state"));
		}	
		if($(".lx-pagination ul").attr("data-table") === "expenses"){
			loadExpenses($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "reclamations"){
			loadReclamations($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
			loadSMSDevices($(".lx-pagination ul").attr("data-state"));
		}
		if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
			loadSMSModels($(".lx-pagination ul").attr("data-state"));
		}	
		$('html, body').animate({
			scrollTop: $(".lx-main-content").offset().top
		}, 1000);
	}
});

$("#pgbumber").on("change",function(){
	filterClicked = 'no';
	$(".lx-pagination ul").attr("data-start",parseInt($(".lx-pagination ul").attr("data-nbpage")) * $(this).val())
	$(".lx-pagination ul li span ins").text(parseInt($(this).val()) + 1);
	if((parseInt($(this).val()) + 1) >= (parseInt($(".lx-pagination ul").attr("data-posts")) / parseInt($(".lx-pagination ul").attr("data-nbpage")))){
		$(".lx-pagination ul li .previous").removeClass("disabled");
		$(".lx-pagination ul li .next").addClass("disabled");
	}
	else if(parseInt($(this).val() + 1) === 1 ){
		$(".lx-pagination ul li .previous").addClass("disabled");
		$(".lx-pagination ul li .next").removeClass("disabled");
	}
	else{
		$(".lx-pagination ul li .previous").removeClass("disabled");
		$(".lx-pagination ul li .next").removeClass("disabled");
	}
	if($(".lx-pagination ul").attr("data-table") === "users"){
		loadUsers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "cities"){
		loadCities($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "dlm"){
		loadDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "subdlm"){
		loadSubDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
		loadShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
		loadGShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "trackingstates"){
		loadTrackingstates($(".lx-pagination ul").attr("data-state"));
	}		if($(".lx-pagination ul").attr("data-table") === "shipramassages"){		loadShipRamassages($(".lx-pagination ul").attr("data-state"));	}
	if($(".lx-pagination ul").attr("data-table") === "clients"){
		loadClients($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "workers"){
		loadWorkers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "clientfees"){
		loadClientfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shipments"){
		loadShipments($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "stocks"){
		loadStocks($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
		loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "bls"){
		loadBLS($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "factures"){
		loadFactures($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "notices"){
		loadNotices($(".lx-pagination ul").attr("data-state"));
	}	
	if($(".lx-pagination ul").attr("data-table") === "expenses"){
		loadExpenses($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "reclamations"){
		loadReclamations($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
		loadSMSDevices($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
		loadSMSModels($(".lx-pagination ul").attr("data-state"));
	}	
	$('html, body').animate({
		scrollTop: $(".lx-main-content").offset().top
	}, 1000);
});

$("body").delegate(".lx-floating-response","click",function(){
	$(".lx-floating-response").fadeOut();
});

$("body").delegate(".lx-first-tr input[name='selectall']","click",function(){
	var checked = $(this).prop("checked");
	$(".lx-table input[type='checkbox']").each(function(){
		$(this).prop("checked",checked)
	});
	var ids = "0";
	$("input[type='checkbox'][name='command']").each(function(){
		if($(this).prop("checked") === true){
			ids += ","+$(this).val();
		}
	});
	$("#idticket").val(ids);
});

$(".lx-action-bulk a").on("click",function(){
	var ids = "0";
	$(".lx-main .lx-table input[type='checkbox']:checked").each(function(){
		if($(this).val() !== "selectall"){
			ids += "," + $(this).val();
		}
	});
	var table = $(".lx-pagination ul").attr("data-table");
	if($(".lx-pagination ul").attr("data-table") === "dlm" || $(".lx-pagination ul").attr("data-table") === "client" || $(".lx-pagination ul").attr("data-table") === "subdlm"){
		table = "users";
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		table = "commands";
	}
	var column = "id";
	if(ids !== "0" && $(".lx-action-bulk select[name='statebulk']").val() !== ""){
		var ajaxurl = "ajax.php";
		$.ajax({
			url : ajaxurl,
			type : 'post',
			data : {
				ids : ids,
				table : table,
				column : column,
				state : $(".lx-action-bulk select[name='statebulk']").val(),
				action : 'updatebulk'
			},
			success : function(response){
				if($(".lx-pagination ul").attr("data-table") === "users"){
					loadUsers($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "cities"){
					loadCities($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "dlm"){
					loadDLM($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "subdlm"){
					loadSubDLM($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
					loadShippingfees($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
					loadGShippingfees($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "trackingstates"){					loadTrackingstates($(".lx-pagination ul").attr("data-state"));				}								if($(".lx-pagination ul").attr("data-table") === "shipramassages"){					loadShipRamassages($(".lx-pagination ul").attr("data-state"));				}
				if($(".lx-pagination ul").attr("data-table") === "clients"){
					loadClients($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "workers"){
					loadWorkers($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "clientfees"){
					loadClientfees($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "shipments"){
					loadShipments($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "stocks"){
					loadStocks($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
					loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "ramassage"){
					loadRamassage($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "commands"){
					loadCommands($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "bls"){
					loadBLS($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "factures"){
					loadFactures($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "notices"){
					loadNotices($(".lx-pagination ul").attr("data-state"));
				}	
				if($(".lx-pagination ul").attr("data-table") === "expenses"){
					loadExpenses($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "reclamations"){
					loadReclamations($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
					loadSMSDevices($(".lx-pagination ul").attr("data-state"));
				}
				if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
					loadSMSModels($(".lx-pagination ul").attr("data-state"));
				}	
			}
		});
	}
});

$(".lx-action-bulk select[name='nbrows']").on("change",function(){
	$(".lx-pagination ul").attr("data-nbpage",$(this).val());
	var ajaxurl = "ajax.php";
	$.ajax({
		url : ajaxurl,
		type : 'post',
		data : {
			nbrows : $(".lx-pagination ul").attr("data-nbpage"),
			action : 'editnbrows'
		},
		success : function(response){}
	});
	if($(".lx-pagination ul").attr("data-table") === "users"){
		loadUsers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "cities"){
		loadCities($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "dlm"){
		loadDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "subdlm"){
		loadSubDLM($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shippingfees"){
		loadShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "gshippingfees"){
		loadGShippingfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "trackingstates"){		loadTrackingstates($(".lx-pagination ul").attr("data-state"));	}		if($(".lx-pagination ul").attr("data-table") === "shipramassages"){		loadShipRamassages($(".lx-pagination ul").attr("data-state"));	}
	if($(".lx-pagination ul").attr("data-table") === "clients"){
		loadClients($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "workers"){
		loadWorkers($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "clientfees"){
		loadClientfees($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "shipments"){
		loadShipments($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "stocks"){
		loadStocks($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "spreadsheets"){
		loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "ramassage"){
		loadRamassage($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "commands"){
		loadCommands($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "bls"){
		loadBLS($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "factures"){
		loadFactures($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "notices"){
		loadNotices($(".lx-pagination ul").attr("data-state"));
	}	
	if($(".lx-pagination ul").attr("data-table") === "expenses"){
		loadExpenses($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "reclamations"){
		loadReclamations($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsdevices"){
		loadSMSDevices($(".lx-pagination ul").attr("data-state"));
	}
	if($(".lx-pagination ul").attr("data-table") === "smsmodels"){
		loadSMSModels($(".lx-pagination ul").attr("data-state"));
	}	
});

$("body").mouseup(function (e){
	if($(".lx-advanced-select").length){
		var elToHide = $(".lx-advanced-select > div");
		var elPreventHide = $(".lx-advanced-select *");
		if (!elPreventHide.is(e.target)){
			elToHide.hide();
		}
	}
});

function isNotEmpty(el){
	var val = el.val();
	if(typeof el.attr("data-id") !== typeof undefined && el.attr("data-id") !== false){
		val = el.attr("data-id");
	}
	if(val === "" || val === "0:0:0"){
		if(el.parent().find("ins").length === 0){
			el.after("<ins>"+el.attr("data-message")+"</ins>");
			el.css("border-color","#d63232");
		}
		return false;
	}
	else{
		el.removeAttr("style");
		return true;
	}
}

$("*[data-isnotempty]").on("keyup blur paste",function(){
	if($(this).val() !== "" && $(this).parent().find("ins").length){
		$(this).parent().find("ins").remove();
		$(this).removeAttr("style");
	}
});

function isNumber(el){
	var regex = /^[0-9\,\.]+$/;
	if(!regex.test(el.val())){
		if(el.parent().find("ins").length === 0){
			el.after("<ins>"+el.attr("data-message")+"</ins>");
			el.css("border-color","#d63232");
		}
		return false;
	}
	else{
		el.removeAttr("style");
		return true;
	}
}

$("*[data-isnumber]").on("keyup blur paste",function(){
	var regex = /^[0-9\,\.]+$/;
	if(regex.test($(this).val()) && $(this).parent().find("ins").length){
		$(this).parent().find("ins").remove();
		$(this).removeAttr("style");
	}
});

function isPhone(el){
	var regex = /^0[0-9]{1}[0-9]{8}$/;
	if(!regex.test(el.val())){
		if(el.parent().find("ins").length === 0){
			el.after("<ins>"+el.attr("data-message")+"</ins>");
			el.css("border-color","#d63232");
		}
		return false;
	}
	else{
		el.removeAttr("style");
		return true;
	}
}

$("*[data-isphone]").on("keyup blur paste",function(){
	var regex = /^0[0-9]{1}[0-9]{8}$/;
	if(regex.test($(this).val()) && $(this).parent().find("ins").length){
		$(this).parent().find("ins").remove();
		$(this).removeAttr("style");
	}
});

function isEmail(el){
	var regex = /^[a-z0-9\._-]+@[a-z0-9\._-]{2,}\.[a-z]{2,4}$/;
	if(!regex.test(el.val())){
		if(el.parent().find("ins").length === 0){
			el.after("<ins>"+el.attr("data-message")+"</ins>");
			el.css("border-color","#d63232");
		}
		return false;
	}
	else{
		el.removeAttr("style");
		return true;
	}
}

$("*[data-isemail]").on("keyup blur paste",function(){
	var regex = /^[a-z0-9\._-]+@[a-z0-9\._-]{2,}\.[a-z]{2,4}$/;
	if(regex.test($(this).val()) && $(this).parent().find("ins").length){
		$(this).parent().find("ins").remove();
		$(this).removeAttr("style");
	}
});

function isPassword(el){
	if(el.val().length < 6){
		if(el.parent().find("ins").length === 0){
			el.after("<ins>"+el.attr("data-message")+"</ins>");
			el.css("border-color","#d63232");
		}
		return false;
	}
	else{
		el.removeAttr("style");
		return true;
	}
}

$("*[data-ispassword]").on("keyup blur paste",function(){
	if($(this).val().length >= 6 && $(this).parent().find("ins").length){
		$(this).parent().find("ins").remove();
		$(this).removeAttr("style");
	}
});

function toDropDown(){
	$(".todropdown").each(function(){
		$(this).parent().find(".todropdowninput").css("color","#AEAEAE");
		$(this).parent().find(".lx-autocomplete-advanced").remove();
		if($(this).prop("disabled") === false){
			$(this).parent().find(".todropdowninput").remove();
			var allowtyping = "";
			/*if(typeof $(this).attr("data-allowtyping") !== typeof undefined && $(this).attr("data-allowtyping") !== false){
				if($(this).attr("data-allowtyping") === "false"){
					allowtyping = "readonly";
				}
			}*/
			var html = '<i class="fa fa-angle-down"></i><input type="text" name="'+$(this).attr("name")+'" value="'+$(this).find("option:selected").text()+'" placeholder="'+$(this).find("option:eq(0)").text()+'" class="todropdowninput" '+allowtyping+' />';
			html += '<div class="lx-autocomplete-advanced">';
			$(this).find("option").each(function(){
				html += '<span href="javascript:;" data-id="'+$(this).attr("value")+'">'+$(this).text()+'</span>';
			});
			html += '</div>';
			$(this).before(html);
			$(this).parent().find(".todropdowninput").css("color","#242424");
		}
	});
}

$(".lx-popup-content").delegate(".lx-autocomplete-advanced span","click",function(){
	if($(this).attr("data-id") !== ""){
		$(this).parent().prev("input").val($(this).text());
	}
	else{
		$(this).parent().prev("input").val("");
	}
	$(this).parent().next("select").val($(this).attr("data-id"));
	$(this).parent().hide();
	$(this).parent().next("select").trigger("keyup");
});

$(".lx-popup-content").delegate(".todropdowninput","mousedown",function(){
	$(this).next(".lx-autocomplete-advanced").show();
});

$(".lx-popup-content").delegate(".todropdowninput","keyup",function(){
	var val = $(this).val();
	var valeur = new RegExp(val,'i');
	$(this).next(".lx-autocomplete-advanced").find("span").each(function(){
		if(valeur.test($(this).text())){
			$(this).css("display","block");
		}
		else{
			$(this).css("display","none");
		}
	});
});

$("body").delegate(".lx-autocomplete-advanced span","click",function(){
	if($(this).attr("data-id") !== ""){
		$(this).parent().prev("input").val($(this).text());
	}
	else{
		$(this).parent().prev("input").val("");
	}
	$(this).parent().next("select").val($(this).attr("data-id"));
	$(this).parent().hide();
	$(this).parent().next("select").trigger("keyup");
});

$("body").delegate(".todropdowninput","mousedown",function(){
	$(this).next(".lx-autocomplete-advanced").show();
});

$("body").delegate(".todropdowninput","keyup",function(){
	var val = $(this).val();
	var valeur = new RegExp(val,'i');
	$(this).next(".lx-autocomplete-advanced").find("span").each(function(){
		if(valeur.test($(this).text())){
			$(this).css("display","block");
		}
		else{
			$(this).css("display","none");
		}
	});
});
