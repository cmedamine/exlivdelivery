<?php
session_start();
header("Content-type: text/css; charset: UTF-8");
include("../config.php");
?>

.lx-wrapper{
	position:relative;
}
.lx-left-bg{
	position:fixed;
	top:0px;
	left:0px;
	display:flex;
	width:35%;
	height:100%;
	background:#111E3D;
}
.lx-right-bg{
	position:fixed;
	top:0px;
	right:0px;
	width:65%;
	height:100%;
	background:url('../images/bg.jpg') no-repeat;
	background-size:cover;
}
@media(max-width:1023px){
	.lx-left-bg{
		width:100%;
		padding:20px;
	}
	.lx-right-bg{
		display:none;
	}
}
.lx-right-bg-shadow{
	position:absolute;
	top:0px;
	left:0px;
	width:100%;
	height:100%;
}
.lx-right-bg-shadow div{
	position:absolute;
	bottom:40px;
	right:40px;
	color:#FFFFFF;
	text-align:right;
}
.lx-right-bg-shadow div h3{
	font-family:'Cairo';
	font-weight:'500';
	font-size:30px;
}
.lx-login{
	margin:auto;
}
.lx-login-content{
	max-width:420px;
	margin:auto;
	color:#FFFFFF;
}
.lx-login-content img{
	display:block;
	width:70%;
	margin:0px auto 20px;
}
.lx-login-content h1{
	margin-bottom:20px;
	font-family:Bitter;
	font-size:40px;
	text-transform:uppercase;
}
.lx-login-content h2{
	margin-bottom:40px;
	font-family:Cairo;
	font-size:24px;
	text-transform:uppercase;
}
.lx-password-forgotten{
	float:right;
	color:#EC7C27;
}
.lx-login-error{
	margin-bottom:20px;
	padding:10px;
	color:#CC0000;
	border:1px solid #CC0000;
}
.lx-header{
	position:fixed;
	z-index:10000;
	top:0px;
	left:0px;
	width:100%;
	height:70px;
	background:#FFFFFF;
	box-shadow:0 2px 2px rgba(0,0,0,0.05), 0 1px 0 rgba(0,0,0,0.05);
}
.lx-header-content{
	padding:15px 20px;
}
.lx-mobile-menu i{
	display:inline-block;
	margin-top:2px;
	font-size:40px;
	color:#242424;
}
.lx-logo{
	padding:24px 10px;
	text-align:center;
	border-bottom:1px solid rgba(255,255,255,0.1);
}
.lx-logo a{
	display:inline-block;
	font-family:'Righteous';
	font-size:22px;
	line-height:20px;
	text-transform:uppercase;
	color:#FFFFFF;
}
.lx-logo .lx-logo-image{
	display:block;
	width:150px;
	height:auto;
}
.lx-header-admin{
	float:right;
}
.lx-header-admin ul li{
	position:relative;
	float:left;
}
.lx-header-admin ul li span{
	display:block;
	margin-right:10px;
	padding:10px;
	font-size:14px;
	font-weight:500;
	background:#F8F8F8;
	border-radius:4px;
	box-shadow:0px 1px 3px #DDDDDD;
	cursor:pointer;
}
.lx-header-admin > ul > li > a{
	display:inline-block;
	padding:5px 10px;
	font-size:24px;
	color:#242424;
}
.lx-header-admin > ul > li > img{
	display:block;
	width:40px;
	height:40px;
	margin-left:10px;
	border-radius:50%;
	box-shadow:0px 0px 10px #EEEEEE;
	cursor:pointer;
}
.lx-account-settings{
	position:absolute;
	z-index:9999;
	top:60px;
	right:0px;
	display:none;
	width:240px;
	background:#FFFFFF;
	box-shadow:0px 0px 10px #BEBEBE;
	border-radius:10px;
	overflow:hidden;
	pointer-events:auto;
}
.lx-account-settings div{
	padding:15px;
	background:#FCFCFC;
	border-bottom:1px solid #d3d3d3;
}
.lx-account-settings .lx-account-identity{
	padding:15px;
	background:#FCFCFC;
	border-bottom:1px solid #d3d3d3;
}
.lx-account-settings div strong{
	font-weight:500;
	color:#828282;
}
.lx-account-settings div strong i{
	font-size:12px;
	font-weight:300;
}
.lx-account-settings div p{
	font-size:13px;
	font-weight:300;
	color:#BEBEBE;
}
.lx-account-settings a{
	display:block;
	padding:13px 15px;
	color:#242424;
	border-bottom:1px solid #EEEEEE;
}
.lx-account-settings a:hover{
	background:#F8F8F8;
}
.lx-account-settings a i{
	display:inline-block;
	width:20px;
}
.lx-notifications-bell ins{
	position:absolute;
	top:-3px;
	left:20px;
	padding:3px;
	font-size:11px;
	font-weight:600;
	background:#FF0000;
	color:#FFFFFF;
	border-radius:4px;
}
.lx-notifications{
	position:absolute;
	top:60px;
	right:0px;
	display:none;
	width:300px;
	max-height:340px;
	background:#FFFFFF;
	box-shadow:0px 0px 10px #BEBEBE;
	border-radius:10px;
	overflow:auto;
}
.lx-notifications-item{
	padding:10px;
	background:#FCFCFC;
	border-bottom:1px dashed #EEEEEE;
}
.lx-notifications-item a{
	color:#242424;
}
.lx-notifications-item a p{
	font-weight:300;
	margin:3px 0px 5px;
	color:#828282;
}
.lx-notifications-item a ins{
	display:block;
	font-size:10px;
	font-style:italic;
	text-align:right;
	color:#BEBEBE;
}
.lx-main-leftside{
	position:fixed;
	z-index:10001;
	top:0px;
	left:0px;	
	width:230px;
	height:100%;
	background:#111E3D;
	transition:all ease 0.3s;
	overflow:auto;
}
@media(max-width:1023px){
	.lx-main-leftside{
		left:-230px;
	}
}
.lx-main-menu{
	position:relative;
}
.lx-mobile-menu-hide{
	position:absolute;
	top:10px;
	right:10px;
	display:none;
	color:#FFFFFF;
}
@media(max-width:1023px){
	.lx-mobile-menu-hide{
		display:block;
	}
}
.lx-main-menu ul li{
	position:relative;
}
.lx-main-menu ul li > i{
	position:absolute;
	top:14px;
	right:15px;
	cursor:pointer;
	color:#FFFFFF;
}
.lx-main-menu ul li a{
	display:block;
	margin:5px;
	padding:10px;
	line-height:14px;
	font-weight:500;
	background:rgba(0,0,0,0.05);
	color:#FFFFFF;
	border-radius:6px;
}
.lx-main-menu ul li a:hover{
	background:rgba(0,0,0,0.1);
}
.lx-main-menu ul li a.active{
	background:rgba(0,0,0,0.2);
}
.lx-main-menu ul li a img{
	position:relative;
	top:-6px;
	float:left;
	margin-right:10px;
	width:24px;
}
.lx-main-menu ul li a i{
	display:inline-block;
	width:30px;	
	font-size:16px;
	text-align:center;
}
.lx-main-menu ul li a span{
	display:none;
	padding:2px 5px 1px;
	font-size:12px;
	background:#CC0000;
	color:#FFFFFF;
	border-radius:4px;
}
.lx-main-menu ul li ul{
	display:none;
}
.lx-main-menu ul li ul li a{
	padding-left:40px;
	background:none;
}
.lx-main-content{
	padding:70px 0px 0px 230px;
	transition:all ease 0.3s;
}
@media(max-width:1023px){
	.lx-main-content{
		padding:70px 0px 0px 0px;
	}
}

@media(max-width:1023px){
	.lx-filter{
		display:none;
		padding-right:15px;
	}
}
.lx-show-filter{
	display:none;
}
@media(max-width:1023px){
	.lx-show-filter{
		display:inline-block;
		margin:15px;
		text-decoration:underline;
		color:#EC7C27;
	}
}
.lx-kpi{
	padding:15px;
	padding-right:0px;
}
@media(max-width:1023px){
	.lx-kpi{
		padding:15px;
	}
}
.lx-kpi-item{
	float:left;
	position:relative;
	width:18.5%;
	margin-right:1.5%;
	padding:20px;
	background:#242424;
	color:#FFFFFF;
}
.lx-2b3544{
	background:#2b3544;
}
.lx-d11141{
	background:#d11141;
}
.lx-00b159{
	background:#EC7C27;
}
.lx-00aedb{
	background:#00aedb;
}
.lx-f37735{
	background:#f37735;
}
.lx-ffc425{
	background:#ffc425;
}
.lx-kpi-item strong{
	font-size:30px;
}
.lx-kpi-item i{
	position:absolute;
	top:10px;
	right:10px;
	font-size:75px;
	color:rgba(255,255,255,0.2);
}
.lx-chart-container{
	margin:15px;
	margin-top:0px;
}
.lx-data-list{
	background:#FFFFFF;
	border:1px solid #d3d3d3;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-data-list > h3{
	padding:10px;
	text-transform:uppercase;
	background:#F8F8F8;
	border-bottom:1px solid #d3d3d3;
}
.lx-data-list > h2{
	padding:10px;
	font-size:30px;
	text-align:center;
}
.lx-data-list-content{
	max-height:300px;
	overflow:auto;
}
.lx-data-list-content ul li{
	padding:10px;
	font-size:12px;
	border-bottom:1px solid #d3d3d3;
}
.lx-data-list-content ul li span{
	float:right;
}
.lx-data-list-content ul li a{
	color:#EC7C27;
}
.lx-page-header{
	padding:20px;
}
.lx-page-header h2{
	position:relative;
	font-size:18px;
	font-weight:500;
	color:#242424;
	text-transform:uppercase;
}
.lx-page-header p{
	font-size:12px;
	color:#858585;
}
.lx-page-header span{
	font-size:12px;
	color:#242424;
}
#my-notification-button{
	position:absolute;
	top:-4px;
	left:120px;
	width:30px;
	height:30px;
	padding-top:8px;
	font-size:13px;
	font-weight:500;
	text-align:center;
	background:#d3d3d3;
	color:#242424;
	border-radius:50%;
}
.lx-page-header a.lx-header-btn{
	float:right;
	position:relative;
	z-index:2;
	top:-3px;
	display:inline-block;
	padding:8px 10px;
	font-weight:500;
	color:#FFFFFF;
	background:#EC7C27;
}
.lx-submenu li a:hover{
	box-shadow:0px 0px 5px #BEBEBE;
}
.lx-submenu li a.active{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-page-content{
	margin:0px 20px 20px;
	padding:5px;
	background:#FFFFFF;
	border:1px solid rgb(233, 236, 239);
	border-radius:4px;
	box-shadow:0 0 20px 0 rgba(183,190,199,0.15);
}
.lx-demandramassage{
	padding:15px;
	padding-bottom:0px;
}
.lx-demandramassage a.lx-demandramassage-btn{
	display:inline-block;
	margin-bottom:15px;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-demandramassage a.lx-demandramassage-btn:hover{
	background:#EC7C27;
}
.lx-add-form label.lx-date{
	position:relative;
	display:inline-block;	
}
.lx-add-form label i{
	position:absolute;	
	top:10px;
	right:10px;
	font-size:15px;
	color:#828282;
}
.lx-add-form label i.fa-angle-down{
	top:31px;
}
.lx-add-form label input[type='text']{
	width:100%;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
	box-shadow:0px 0px 5px #F8F8F8 inset;	
}
.lx-add-form h3{
	margin-bottom:15px;
	font-size:14px;
	font-weight:500;
}
.lx-add-form a.lx-new{
	position:relative;
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
@media(max-width:768px){
	.lx-add-form a.lx-new{
		display:block;
		margin-bottom:5px;
	}
}
.lx-add-form a.lx-new:hover{
	background:#EC7C27;
}
.lx-add-form a.lx-new input{
	position:absolute;
	top:0px;
	left:0px;
	width:100%;
	height:100%;
	opacity:0.0;
}
.lx-add-form a.lx-new.lx-error-import{
	background:#FF0000 !important;
}
#editkpiform{
	padding:15px 15px 0px 15px;
}
#editkpiform label{
	position:relative;
	display:inline-block;
	margin-bottom:20px;
	margin-right:20px;
}
.lx-textfield{
	position:relative;
	margin-bottom:15px;
}
.lx-textfield label{
	position:relative;
}
.lx-popup .lx-textfield{
	position:relative;
	margin-bottom:0px;
}
.lx-login .lx-textfield label i{
	position:absolute;
	right:15px;
	top:-2px;
	font-size:20px;
	color:#CCCCCC;
}
.lx-textfield span{
	display:block;
	margin-bottom:5px;
}
.lx-textfield input[type='text']{
	width:100%;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
	box-shadow:0px 0px 5px #F8F8F8 inset;
}
.lx-login .lx-textfield input[type='text']{
	padding:15px 20px;
	background:#F8F8F8;
	border:1px solid #d3d3d3;
	border-radius:0px;
	box-shadow:0px 0px 0px #F8F8F8 inset;
}
.lx-login .lx-textfield input:-webkit-autofill,
.lx-login .lx-textfield input:-webkit-autofill:hover, 
.lx-login .lx-textfield input:-webkit-autofill:focus, 
.lx-login .lx-textfield input:-webkit-autofill:active{
	-webkit-box-shadow: 0 0 0 30px #F8F8F8 inset !important;
}
.lx-textfield input[type='number']{
	width:100%;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
.lx-textfield input[type='password']{
	width:100%;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
.lx-login .lx-textfield input[type='password']{
	padding:15px 20px;
	background:#F8F8F8;
	border:1px solid #d3d3d3;
	border-radius:0px;
	box-shadow:0px 0px 0px #F8F8F8 inset;
}
.lx-textfield select{
	width:100%;
	padding:9px 10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
.lx-textfield textarea{
	width:100%;
	height:100px;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
	resize:none;
}
.lx-textfield ins{
	color:#d63232;
}
.lx-image-picker{
	position:relative;
	display:flex;
	width:100px;
	height:100px;
	margin-bottom:3px;
	background:#FFFFFF;
	border:1px solid #d3d3d3;
	cursor:pointer;
}
@media(max-width:1023px){
	.lx-image-picker{
		width:95px;
		height:95px;
	}
}
.lx-image-picker span{
	display:inline-block;
	margin:auto;
}
.lx-image-picker input{
	display:none;
}
.lx-image-picker img{
	position:absolute;
	width:100%;
	padding:5px;
}
.lx-delete-image{
	display:none;
	font-size:12px;
	color:#CC0000;
}
.lx-submit,.lx-submit-add{
	margin-bottom:15px;
}
.lx-submit a,.lx-submit-add a{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-login .lx-submit{
	margin-top:40px;
}
.lx-login .lx-submit a{
	display:block;
	padding:15px 20px;
	font-size:16px;
	text-align:center;
	background:#EC7C27;
}
.lx-submit a:hover,.lx-submit-add a:hover{
	background:#EC7C27;
}
.lx-submit-add-variant{
	position:relative;
}
.lx-submit-add-variant a{
	position:absolute;
	bottom:0px;
	width:100%;
}
.lx-table{
	width:100%;
	margin-top:10px;
	overflow:auto;
}
.lx-table label{
	position:relative;
	display:inline-block;
	white-space:nowrap;
}
.lx-table p{
	margin-top:20px;
	font-weight:600;
}
.lx-table a.lx-trash{
	float:right;
	display:inline-block;
	margin-bottom:10px;
	padding:5px 10px;
	font-size:12px;
	font-weight:300;
	background:#FDFDFD;
	color:#858585;
	border:1px solid #d3d3d3;
	border-radius:2px;
	box-shadow:0px 0px 5px #F8F8F8 inset;
}
.lx-table a.lx-trash:hover{
	box-shadow:0px 0px 5px #F8F8F8;
}
.lx-table > a{
	float:right;
	display:inline-block;
	margin-bottom:10px;
	padding:5px 10px;
	font-size:12px;
	font-weight:300;
	background:#FDFDFD;
	color:#858585;
	border:1px solid #d3d3d3;
	border-radius:2px;
	box-shadow:0px 0px 5px #F8F8F8 inset;
}
.lx-table > a:hover{
	box-shadow:0px 0px 5px #F8F8F8;
}
.lx-table table{
	width:100%;
	background:#FFFFFF;
}
.lx-table table tr:nth-child(2n+1){
	background:#FAFAFA;
}
.lx-table table tr td{
	padding:10px;
	font-weight:300;
	color:#858585;
	border-right:1px solid #d3d3d3;
}
.lx-table table tr td:first-child{
	width:1%;
	border-left:1px solid #d3d3d3;
}
.lx-table table tr td:last-child{
	width:1%;
	border-right:1px solid #d3d3d3;
	white-space:nowrap;
}
.lx-table table tr:first-child td{
	padding:10px;
	font-weight:500;
	color:#242424;
	background:#eceff2;
	border-top:1px solid #d3d3d3;
	border-bottom:1px solid #d3d3d3;
	white-space:nowrap;
}
.lx-table table tr:last-child td{
	border-bottom:1px solid #d3d3d3;
}
.lx-table table tr td img.lx-avatar{
	float:left;
	width:36px;
	height:36px;
	margin-right:8px;
	border-radius:50%;
}
.lx-table table tr td span{
	display:block;
	margin-bottom:2px;
	color:#242424;
}
.lx-table table tr td img.lx-avatar{
	float:left;
	width:40px;
	height:40px;
	border-radius:50%;
}
.lx-table table tr td a{
	color:#242424;
}
.lx-table table tr td > a{
	font-size:12px;
	color:#EC7C27;
}
.lx-table table tr td a.lx-delete{
	color:#CC0000;
}
.lx-table table tr td:last-child a{
	font-weight:500;
}
.lx-table table tr td del{
	text-decoration:line-through;
}
.lx-table table tr td select{
	padding:7px 10px;
	border:1px solid #d3d3d3;
}
.lx-table table tr td p{
	padding:5px 0px;
	color:#242424;
	border-bottom:1px dashed #d3d3d3;
}
.lx-table table tr td p:last-of-type{
	border-bottom:0px;
}
.lx-table table tr td p ins{
	display:block;
	font-style:italic;
	text-align:right;
	color:#828282;
}
.lx-on-off-fill{
	position:relative;
	width:35px;
	height:14px;
	background:#d3d3d3;
	border-radius:10px;
	cursor:pointer;
	-webkit-transition:all ease 0.3s;
	transition:all ease 0.3s;
}
.lx-on-off-blue .lx-on-off-fill{
	background:#00d8f5;
}
.lx-on-off span{
	position:absolute;
	left:0px;
	top:-3px;
	display:inline-block;
	width:20px;
	height:20px;
	background:#DDDDDD;
	border-radius:50%;
	box-shadow:0px 0px 10px #DDDDDD;
	cursor:pointer;
	-webkit-transition:all ease 0.3s;
	transition:all ease 0.3s;
}
.lx-on-off-fill i{
	float:left;
	display:inline-block !important;
	margin-left:3px !important;
	font-size:11px !important;
	color:#FFFFFF !important;
}
.lx-on-off-blue span{
	left:15px;
	background:#00b0c8;
}
.lx-pagination{
	margin:20px 0px;
}
.lx-pagination ul{
	float:right;
}
.lx-pagination ul li{
	float:left;
	margin-left:20px;
}
.lx-pagination ul li span{
	display:inline-block;
	padding:10px 0px;
	font-size:14px;
	font-weight:500;
	text-align:center;
	color:#242424;
	border-radius:50%;
}
.lx-pagination ul li a{
	display:inline-block;
	width:33px;
	padding:5px 0px;
	font-size:16px;
	font-weight:500;
	text-align:center;
	color:#424242;
	border:2px solid transparent;
	border-radius:50%;
}
.lx-pagination ul li a.previous{
	border:2px solid #424242;
}
.lx-pagination ul li a.next{
	border:2px solid #424242;
}
.lx-pagination ul li a.disabled{
	border:2px solid #BEBEBE;
	color:#BEBEBE;
	cursor:initial;
}
.lx-pagination ul li select{
	padding:8px;
	padding-right:0px;
	border:2px solid #424242;
	border-radius:6px;
}
.lx-medias-toolbar{
	position:relative;
	margin-top:15px;
	margin-left:15px;
	padding:15px;
	background:#FDFDFD;
	border:1px solid #d3d3d3;
}
@media(max-width:1023px){
	.lx-medias-toolbar{
		margin:15px;
	}
}
.lx-medias-toolbar a{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-medias-toolbar:hover a{
	background:#EC7C27;
}
.lx-medias-toolbar input{
	position:absolute;
	z-index:2;
	top:15px;
	left:15px;
	width:107px;
	height:35px;
	overflow:hidden;
	opacity:0.0;
	cursor:pointer;
}
@media(max-width:1023px){
	.lx-medias-list .lx-g6{
		width:50% !important;
	}
}
.lx-medias-item{
	position:relative;
}
.lx-medias-item img{
	display:block;
	width:100%;
	padding:5px;
	background:#FFFFFF;
	border:1px solid #d3d3d3;
}
.lx-medias-item i{
	position:absolute;
	top:10px;
	right:10px;
	font-size:16px;
	background:#CC0000;
	color:#FFFFFF;
	border-radius:50%;
	cursor:pointer;
}
.lx-medias-library{
	position:fixed;
	z-index:11;
	top:0px;
	left:0px;
	display:none;
	width:100%;
	height:100%;
	padding:30px;
	background:#F8F8F8;
	overflow:auto;
}
@media(max-width:1023px){
	.lx-medias-library .lx-g6{
		width:50% !important;
	}
}
.lx-medias-library > i{
	position:absolute;
	top:10px;
	right:10px;
	font-size:26px;
	cursor:pointer;
}
.lx-medias-library-item{
	position:relative;
	cursor:pointer;
}
.lx-medias-library-item img{
	display:block;
	width:100%;
	padding:5px;
	background:#FFFFFF;
	border:1px solid #d3d3d3;
}

.lx-floating-response{
	position:fixed;
	z-index:99999;
	bottom:20px;
	left:0px;
	display:none;
	padding:0px 20px;
	width:100%;
	text-align:center;
	cursor:pointer;
}
.lx-floating-response p{
	position:relative;
	display:inline-block;
	padding:17px 20px 20px;
	font-size:16px;
	background:rgba(0,0,0,0.8);
	color:#FFFFFF;
	border-radius:4px;
}
.lx-floating-response p.lx-error{
	border-bottom:3px solid #e50000;
}
.lx-floating-response p.lx-succes{
	border-bottom:3px solid #EC7C27;
}
.lx-floating-response p i{
	position:relative;
	top:6px;
	line-height:1px;
}
.lx-floating-response p i:last-child{
	position:absolute;
	right:3px;
	top:10px;
	font-size:16px;
	line-height:1px;
}
.lx-floating-response p i.fa{
	top:2px;
}

.lx-autocomplete{
	position:absolute;
	z-index:3;
	top:100%;
	left:0px;
	width:100%;
	max-height:200px;
	box-shadow:0px 0px 4px #BEBEBE;
	overflow:auto;
}
.lx-autocomplete a{
	display:block;
	padding:10px;
	font-size:13px;
	background:#FFFFFF;
	color:#242424;
	word-break: break-all
}
.lx-autocomplete a:hover{
	background:#F8F8F8;
}
.lx-command-edit{
	position:fixed;
	z-index:10;
	top:0px;
	left:0px;
	display:none;
	width:100%;
	height:100%;
	background:rgba(0,0,0,0.5);
	overflow:auto;
}
.lx-command-edit-inside{
	position:relative;
	max-width:800px;
	padding:20px;
	margin:auto;
	background:#FFFFFF;
}
@media(max-width:1023px){
	.lx-command-edit-inside{
		width:100%;
	}
}
.lx-command-edit-inside > a{
	position:absolute;
	top:10px;
	right:10px;
	color:#242424;
	
}
.lx-tracking-edit{
	position:fixed;
	z-index:10;
	top:0px;
	left:0px;
	display:none;
	width:100%;
	height:100%;
	background:rgba(0,0,0,0.5);
	overflow:auto;
}
.lx-tracking-edit-inside{
	position:relative;
	max-width:800px;
	padding:20px;
	margin:auto;
	background:#FFFFFF;
}
@media(max-width:1023px){
	.lx-tracking-edit-inside{
		width:100%;
	}
}
.lx-tracking-edit-inside > a{
	position:absolute;
	top:10px;
	right:10px;
	color:#242424;
	
}
.lx-ip-exists{
	display:inline-block;
	margin:3px 0px;
	padding:1px 3px;
	font-size:12px;
	font-weight:500;
	color:#FFFFFF;
	background:#EE0000;
	border-radius:4px;
}
.lx-ip-new{
	display:inline-block;
	margin:3px 0px;
	padding:1px 3px;
	font-size:12px;
	font-weight:500;
	color:#FFFFFF;
	background:#EC7C27;
	border-radius:4px;
}
.lx-cleaner{
	padding:15px;
	text-align:center;
}
.lx-cleaner h3{
	padding:15px;
	margin:30px 0px;
}
.lx-cleaner h4 i{
	color:#EC7C27;
}
.lx-loading{
	position:absolute;
	z-index:100;
	top:0px;
	left:0px;
	display:flex;
	width:100%;
	height:100%;
	padding:30px;
	text-align:center;
	background:rgba(255,255,255,0.5);
}
.lx-loading p{
	margin:auto;
}
.lx-loading p i{
	display:inline-block;
	margin-top:5px;
	font-size:30px;
}
.lx-tracking-stats-item{
	padding:20px;
	text-align:center;
	color:#FFFFFF;
}
.lx-tracking-stats-item strong{
	display:block;
	font-size:22px;
}
/* Popup */
.lx-popup{
	position:fixed;
	top:0px;
	left:0px;
	z-index:99999;
	width:100%;
	height:100%;
	background:rgba(0,0,0,0.7);
	display:none;
	overflow:auto;
}
.lx-popup-inside{
	display:flex;
	height:100%;
}
.lx-popup-content{
	position:relative;
	display:table;
	margin:auto;
	opacity:0.0;
	transform:scale(0.9);
	-webkit-transition: all 0.5s;
	transition: all 0.5s;
}
.lx-popup-content > a{
	position:absolute;
	right:8px;
	top:8px;
	color:#242424;
}
.lx-form{
	width:700px;
	background:#FFFFFF;
}
@media(max-width:1023px){
	.lx-form{
		min-width:100%;
		width:100%;
	}
}
.lx-form-title{
	padding:10px;
	text-align:center;
	background:#F8F8F8;
}
.lx-popup .lx-add-form{
	padding:15px;
}
.lx-keyword-rate{
	float:right;
}
@media(max-width:768px){
	.lx-keyword{
		float:none;
		clear:both;
	}
}
.lx-keyword label{
	position:relative;
	display:inline-block;
	margin-right:10px;
	margin-bottom:10px;
}
@media(max-width:1023px){
	.lx-keyword label{
		display:block;
		margin-bottom:10px;
	}
}
.lx-keyword label input[type='text']{
	width:185px;
	padding:10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
.lx-keyword label select{
	width:200px;
	padding:9px 10px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
@media(max-width:768px){
	.lx-keyword label{
		margin-right:0px;
	}
	.lx-keyword label input[type='text']{
		width:100%;
	}
	.lx-keyword label select{
		width:100%;
	}
}
.lx-delivarymen-cities{
	margin-top:10px;
}
.lx-delivarymen-cities li{
	display:inline-block;
	margin-right:5px;
	padding:5px;
	background:#F8F8F8;
	border-radius:2px;
}
.lx-delivarymen-cities li i{
	float:right;
	display:inline-block;
	margin-left:10px;
	font-size:12px;
	background:#d3d3d3;
	border-radius:50%;
	cursor:pointer;
}
.lx-colihistory{
	margin-left:10px;
	padding:10px;
	font-size:13px;
	color:#828282;
	border-left:1px solid #d3d3d3;
}
.lx-colihistory li{
	margin-bottom:20px;
}
.lx-colihistory li:last-child{
	margin-bottom:0px;
}
.lx-colihistory li span{
	position:relative;
	display:block;
	color:#242424;
	font-weight:500;
}
.lx-colihistory li span::before{
	position:absolute;
	left:-13px;
	top:5px;
	content:'';
	display:block;
	width:6px;
	height:6px;
	background:#242424;
	border-radius:50%;
}
.lx-colihistory li > ins{
	display:inline-block;
}
.lx-delete-history{
	font-weight:400;
	color:#CC0000;
}
.lx-first-tr i{
	cursor:pointer;
}
.lx-textfield input[type='file']{
	position:absolute;
	z-index:2;
	top:0px;
	left:0px;
	width:107px;
	height:35px;
	overflow:hidden;
	opacity:0.0;
	cursor:pointer;
}
.lx-medias-item{
	position:relative;
	width:100px;
	height:100px;
	margin-bottom:10px;
}
.lx-medias-item img{
	display:block;
	width:100%;
	padding:5px;
	background:#FFFFFF;
	border:1px solid #d3d3d3;
}
input[readonly]{
	background:#F8F8F8;
	cursor:not-allowed;
}
.detailsreclamation .lx-add-form{
	padding:20px;
}
.detailsreclamation .lx-add-form p{
	margin-bottom:10px;
}
.lx-message-detail{
	padding:10px;
	background:#F8F8F8;
	border-radius:4px;
	border:1px solid #d3d3d3;
}
.lx-states-count h3 a{
	color:#BEBEBE;
}
.lx-states-count h3 a:hover{
	color:#828282;
}
.lx-state-count{
	background:#FBFBFB;
	border:1px solid rgb(233, 236, 239);
	/* box-shadow:0 0 20px 0 rgba(183,190,199,0.15); */
}
.lx-state-count a{
	display:block;
	padding:10px;
	text-align:center;
	color:#242424;
}
.lx-state-count a span{
	display:block;
	color:#242424;
	white-space:nowrap;
}
.lx-state-count a strong{
	font-size:20px;
}
.lx-state-count a ins{
	position:relative;
	display:block;
	height:15px;
	margin-top:7px;
	background:#F8F8F8;
	box-shadow:0px 0px 10px rgba(183,190,199,0.15) inset;
}
.lx-state-count a ins u{
	position:absolute;
	top:0px;
	left:0px;
	height:100%;
}
.lx-state-count a ins del{
	position:absolute;
	bottom:calc(100% + 3px);
	right:0px;
}
.lx-add-form a.lx-import-colis{
	background:#EC7C27;
}
.lx-importer{
	position:relative;
	padding:50px 20px;
	text-align:center;
	background:#F8F8F8;
	color:#BEBEBE;
	border:2px dashed #d3d3d3;
	border-radius:4px;
}
.lx-textfield .lx-importer input[type='file']{
	position:absolute;
	z-index:2;
	top:0px;
	left:0px;
	width:100%;
	height:100%;
	border:1px solid #000000;
	cursor:pointer;
}
.lx-overall-progress{
	background:#FFFFFF;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-textfield a:not(.lx-coli-state-delivarymen,.lx-password-forgotten,.lx-upload-picture){
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#FB404B;
	color:#FFFFFF;
	border-radius:2px;
	box-shadow:0px 0px 0px #BEBEBE inset;
}
.lx-textfield:hover a:not(.lx-coli-state-delivarymen,.lx-password-forgotten,.lx-upload-picture){
	box-shadow:0px 0px 5px #BEBEBE;
}
.lx-coli-state-delivarymen{
	display:inline-block;
	margin-bottom:5px;
	padding:10px;
	border-radius:4px;
}
.lx-log-history{
	background:#FFFFFF;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-log-history h3{
	padding:10px;
	background:#F8F8F8;
}
.lx-log-list{
	height:500px;
	overflow:auto;
}
.lx-log-list ul li{
	padding:10px;
	font-size:12px;
	border-bottom:1px solid #F8F8F8;
}
.lx-log-list ul li span{
	float:left;
	display:inline-block;
	width:80px;
}
.lx-log-list ul li span ins{
	display:inline-block;
	padding:3px;
	color:#FFFFFF;
	border-radius:4px;
}
.lx-log-list ul li > ins{
	color:#424242;
}
.lx-log-list ul li del{
	float:left;
	display:inline-block;
	width:110px;
	padding:3px;
	color:#929292;
}
.substractreturn #substractreturnform p{
	padding:10px;
	background:#F8F8F8;
}
.substractreturn #substractreturnform p:nth-child(2n+2){
	background:#FBFBFB;
}
.lx-delete-box{
	text-align:center;
}
.lx-delete-box p{
	margin-bottom:20px;
	font-size:16px;
}
.lx-delete-box a{
	display:inline-block;
	padding:10px 30px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-details-products ul{
	margin:0px 15px;
}
.lx-details-products ul li{
	clear:both;
	padding:10px;
	background:#FAFAFA;
	border-bottom:2px solid #FFFFFF;
}
.lx-details-products ul li:first-child{
	margin-top:15px;
}
.lx-details-products ul li a{
	float:right;
}
.lx-details-products ul li a i{
	display:inline-block;
	padding:1px;
	font-size:14px;
	background:#DDDDDD;
	color:#242424;
	border-radius:50%;
}
.lx-autocomplete{
	position:absolute;
	z-index:3;
	top:100%;
	left:0px;
	width:100%;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-autocomplete a{
	display:block;
	padding:10px;
	font-size:13px;
	background:#FFFFFF;
	color:#242424;
	word-break: break-all
}
.lx-autocomplete a:hover{
	background:#F8F8F8;
}
.lx-importer{
	position:relative;
	padding:50px 20px;
	text-align:center;
	background:#F8F8F8;
	color:#BEBEBE;
	border:2px dashed #d3d3d3;
	border-radius:4px;
}
.lx-textfield .lx-importer input[type='file']{
	position:absolute;
	z-index:2;
	top:0px;
	left:0px;
	width:100%;
	height:100%;
	border:1px solid #000000;
	cursor:pointer;
}
.lx-overall-progress{
	background:#FFFFFF;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-action-bulk{
	float:left;
	margin-right:10px;
	padding:10px 0px 0px;
}
.lx-action-bulk select{
	padding:9px 11px;
	border:1px solid #d3d3d3;
	border-radius:2px;
}
.lx-action-bulk a{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-action-bulk:hover a{
	background:#EC7C27;
}
tr[data-row]{
	display:none;
}
#commandsform #keywordclienttitle{
	width:76px;
}
#commandsform #keywordclientfullname{
	width:calc(100% - 80px);
}
.lx-thumbs-up{
	color:#EC7C27;
}
.lx-thumbs-down{
	color:#CC0000;
}
.lx-login-error{
	padding:10px;
	background:#CC0000;
	color:#FFFFFF;
}
.lx-table i.fa-edit{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-reply{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-clone{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-paper-plane{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-download{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-comment{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-user-friends{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-clock{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-trash{
	display:inline-block;
	width:24px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-print{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-upload{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-table i.fa-percent{
	display:inline-block;
	width:24px;
	margin-right:10px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-product-info{
	display:none;
}
.lx-command-info{
	display:none;
}
.lx-add-form-nav{
	display:table;
	margin:20px auto;
}
.lx-add-form-nav ul li{
	float:left;
}
.lx-add-form-nav ul li span{
	position:relative;
	display:inline-block;
	width:50px;
	height:50px;
	margin-left:100px;
	padding-top:10px;
	font-size:24px;
	text-align:center;
	background:#EEEEEE;
	color:#242424;
	border-radius:50%;
	cursor:pointer;
}
.lx-add-form-nav ul li:first-child span{
	margin-left:0px;
}
.lx-add-form-nav ul li span.active{
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-add-form-nav ul li span::before{
	content:'';
	position:absolute;
	top:23px;
	right:100%;
	width:100px;
	height:4px;
	background:#EEEEEE;
}
.lx-add-form-nav ul li:first-child span::before{
	display:none;
}
.lx-add-form-nav ul li span.active::before{
	background:#EC7C27;
}
.lx-add-command-product{
	position:relative;
	top:-1px;
	display:inline-block;
	width:30px;
	height:33px;
	text-align:center;
	padding-top:9px;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-add-form .lx-next{
	position:relative;
	z-index:2;
	float:right;
	margin-top:15px;
	margin-right:15px;
}
.lx-add-form .lx-next a{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-show-variants{
	float:right;
	display:inline-block;
	margin:15px 15px 0px 0px;
	color:#EC7C27;
}
#productsform input[name='other']{
	width:calc(100% - 34px);
}
.lx-add-variant-product{
	position:relative;
	top:-1px;
	display:inline-block;
	width:30px;
	height:33px;
	text-align:center;
	padding-top:9px;
	background:#EEEEEE;
	color:#242424;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-variants-list{
	margin-top:15px;
}
.lx-variants-list p{
	margin:2px 15px;
	padding:10px;
	background:#FAFAFA;
	border:1px solid #EEEEEE;
}
.lx-variants-list p a{
	float:right;
}
.lx-variants-list p a i.fa-trash{
	display:inline-block;
	width:auto;
	padding:0px;
	text-align:center;
	background:none;
	color:#242424;
	box-shadow:0px 0px 0px #BEBEBE;
}
.lx-upload-picture{
	display:inline-block;
	padding:10px;
	font-size:13px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;	
}
.daterangepicker{
	z-index:99999 !important;
}
.lx-all-states{
	position:absolute;
	top:-4px;
	left:0px;
	width:calc(100% - 200px);
	background:#FFFFFF;
	border:1px solid #d3d3d3;
	overflow:hidden;
}
.lx-all-states i{
	position:absolute;
	top:0px;
	width:20px;
	height:100%;
	padding-top:10px;
	font-size:14px;
	text-align:center;
	background:#FAFAFA;
	cursor:pointer;
}
.lx-all-states i.fa-caret-left{
	left:0px;
}
.lx-all-states i.fa-caret-right{
	right:0px;
}
.lx-all-states ul{
	margin:0px 15px;
	white-space:nowrap;
	overflow:auto;
}
.lx-all-states ul::-webkit-scrollbar {
    display:none;
}
.lx-all-states ul li{
	display:inline-block;
	border-right:1px solid #d3d3d3;
}
.lx-all-states ul li a{
	display:block;
	padding:10px;
	color:#242424;
}
.lx-all-states ul li a:hover{
	background:#FAFAFA;
}
.lx-all-states ul li a.active{
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-command-history{
	padding:15px;
}
.lx-command-history p{
	position:relative;
	font-weight:500;
	line-height:24px;
	text-align:center;
}
.lx-command-history p:last-of-type::after{
	position:absolute;
	z-index:1;
	bottom:-15px;
	left:50%;
	content:'';
	width:10px;
	height:10px;
	margin-left:-5px;
	background:#828282;
	border-radius:50%;
}
.lx-command-history ul{
	position:relative;
	width:100%;
	margin-top:10px;
	padding:10px 0px;
}
.lx-command-history ul::before{
	position:absolute;
	z-index:1;
	top:0px;
	right:50%;
	content:'';
	width:1px;
	height:100%;
	background:#828282;
}
.lx-command-history ul::after{
	position:absolute;
	z-index:1;
	bottom:0px;
	left:50%;
	content:'';
	width:10px;
	height:10px;
	margin-left:-5px;
	background:#828282;
	border-radius:50%;
}
.lx-command-history ul li{
	margin:20px 0px;
	text-align:center;
}
.lx-command-history ul li span{
	display:inline-block;
	width:200px;
	text-align:right;
}
.lx-command-history ul li span:nth-child(3){
	text-align:left;
}
.lx-command-history ul li ins{
	position:relative;
	z-index:2;
	display:inline-block;
	width:140px;
	padding:5px 0px;
	color:#828282;
	background:#FFFFFF;
}
.lx-tickets-layouts{
	text-align:center;
}
.lx-print-tickets{
	display:inline-block;
	width:92px;
	margin:0px 10px;
	padding:10px;
	text-align:center;
	background:#FBFBFB;
	color:#242424;
	border-radius:4px;
	box-shadow:0px 0px 4px #BEBEBE;
}
.lx-print-tickets .lx-model1{
	display:inline-block;
	width:30px;
	height:22px;
	margin:2px;
	border:2px solid #242424;
}
.lx-print-tickets .lx-model2{
	display:inline-block;
	width:60px;
	height:37px;
	margin:2px;
	border:2px solid #242424;
}
.lx-print-tickets .lx-model3{
	display:inline-block;
	width:30px;
	height:37px;
	margin:2px;
	border:2px solid #242424;
}
.lx-print-tickets .lx-model4{
	display:inline-block;
	width:30px;
	height:15px;
	margin:2px;
	border:2px solid #242424;
}
.lx-print-tickets .lx-model5{
	display:inline-block;
	width:30px;
	height:22px;
	margin:2px;
	border:2px solid #242424;
}
.lx-page-header-command h2{
	float:left;
	margin-top:10px;
}
.lx-page-header-command .lx-add-form{
	float:right;
}
.lx-add-other-stock{
	display:inline-block;
	margin-top:5px;
	margin-left:15px;
	color:#EC7C27;
}
.lx-remove-this-stock{
	float:right;
	display:inline-block;
	margin-top:5px;
	margin-right:15px;
	color:#CC0000;
}
.lx-upload-receipt{
	position:relative;
	width:100px;
}
.lx-upload-receipt input{
	position:absolute;
	top:0px;
	left:0px;
	z-index:2;
	width:100%;
	height:100%;
	opacity:0.0;
	cursor:pointer;
}
.lx-upload-receipt a{
	position:relative;
	z-index:1;
	display:block;
	padding:5px;
	font-weight:500;
	text-align:center;
	background:#EC7C27;
	color:#FFFFFF !important;
}
.lx-receipt-img{
	display:block;
	max-width:100px;
	max-height:100px;
	margin:0px !important;
	margin-bottom:5px !important;
	cursor:zoom-in;
}
.lx-add-form img{
	display:block;
	max-width:100%;
	margin:auto;
}
.lx-stats-bloc{
	max-height:400px;
	margin:0px 5px;
	padding:15px;
	background:#FFFFFF;
	border:1px solid rgb(233, 236, 239);
	border-radius:4px;
	box-shadow:0 0 20px 0 rgba(183,190,199,0.15);
	overflow:auto;
}
.lx-stats-bloc h3{
	margin-bottom:10px;
	text-transform:uppercase;
}
.lx-stats-bloc table{
	width:100%;
}
.lx-stats-bloc table tr:nth-child(2n+2){
	background:#FCFCFC;
}
.lx-stats-bloc table tr:first-child td{
	font-weight:500;
}
.lx-stats-bloc table td{
	padding:10px;
	border-bottom:1px solid #DEDEDE;
}
.lx-stats-bloc table tr:last-child td{
	border-bottom:0px;
}
.lx-stats-bloc table[data-tab]{
	display:none;
}
.lx-stats-bloc table[data-tab='1']{
	display:table;
}
.lx-stats-bloc ul{
	float:left;
	padding:10px 0px;
}
@media(max-width:768px){
	.lx-stats-bloc ul{
		float:none;
		clear:both;
		margin-bottom:10px;
	}
}
.lx-stats-bloc ul li{
	display:inline-block;
}
.lx-stats-bloc ul li a{
	padding:10px;
	font-weight:500;
	background:#FBFBFB;
	color:#242424;
	border-radius:4px;
}
.lx-stats-bloc ul li a.active{
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-chart-container{
	max-height:initial;
}
.lx-advanced-select{
	display:inline-block;
	position:relative;
}
.lx-advanced-select-add{
	display:block;
}
.lx-advanced-select > input[readonly]{
	background:#FFFFFF;
	cursor:pointer;
}
.lx-advanced-select > i{
	position:absolute;
	z-index:2;
	right:2px;
	top:2px;
	padding:9px;
	background:#FFFFFF;
	cursor:pointer;
}
.lx-advanced-select-add > i{
	top:initial !important;
	bottom:2px !important;
	right:2px !important;
}
.lx-advanced-select > div{
	position:absolute;
	z-index:3;
	left:0px;
	top:100%;
	display:none;
	width:100%;
	max-height:300px;
	padding:15px;
	background:#FFFFFF;
	border:1px solid #d3d3d3;
	overflow:auto;
}
.lx-advanced-select > div label{
	position:relative;
	white-space:nowrap;
}
.lx-advanced-select-add > div label{
	display:block;
	margin:10px 0px;
}
.lx-state-filter{
	display:block;
	float:left;
	width:calc(50% - 2px);
	margin-left:4px;
	margin-bottom:10px;
	padding:7px 8px;
	font-weight:500;
	text-align:center;
	background:#EC7C27;
	color:#FFFFFF;
	border:1px solid #EC7C27;
	border-radius:2px;	
}
.lx-state-empty{
	display:block;
	float:left;
	width:calc(50% - 2px);
	margin-bottom:10px;
	padding:7px 8px;
	font-weight:500;
	text-align:center;
	background:#F8F8F8;
	color:#BEBEBE;
	border:1px solid #BEBEBE;
	border-radius:2px;	
}
.lx-search-keyword{
	position:absolute;
	top:2px;
	right:2px;
	padding:7px 8px;
	font-size:16px;
	text-align:center;
	background:#EC7C27;
	color:#FFFFFF;
	border-radius:2px;
}
.lx-search-keyword + input{
	padding-right:40px !important;
}
.lx-sales{
	margin:10px 20px;
	padding:10px;
	background:#fff68f;
	color:#242424;
	border:1px solid #FFA500;
}
.lx-update{
	padding:10px;
	background:#F8F8F8;
	border:1px solid #EEEEEE;
	border-radius:4px;
}
.lx-update-details{
	margin:5px 0px;
	padding:10px;
	background:#EEEEEE;
}
.lx-update a{
	display:inline-block;
	margin-bottom:5px;
	padding:5px 10px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-start-update{
	padding:30px;
	text-align:center;
}
.lx-start-update a{
	display:inline-block;
	margin-top:20px;
	padding:5px 10px;
	font-weight:500;
	background:#EC7C27;
	color:#FFFFFF;
}
.lx-start-update pre{
	margin-top:20px;
	color:#828282;
}
.lx-rib{
	padding:10px;
	margin-bottom:10px;
	font-weight:600;
	background:#F8F8F8;
	border:1px solid #EEEEEE;
	border-radius:4px;;	
}
.lx-notices-item{
	position:relative;
	margin:5px;
	padding:20px;
	border-radius:6px;
	overflow:hidden;
	background:#ebf6fa;
	border:1px solid #39ADD1;	
}
.lx-notices-item p{
	position:relative;
	z-index:2;
	margin-left:50px;
	font-size:16px;
	line-height:24px;
}
.lx-notices-item i.fa{
	position:absolute;
	z-index:1;
	top:5px;
	left:5px;
	font-size:60px;
	opacity:0.05;
}
.lx-reported-blink,.lx-interested-blink,.lx-changeaddress-blink{
	position:relative;
	top:-2px;
	left:40px;
	display:inline-block;
	padding:3px 5px;
	font-size:10px;
	color:#CC0000;
	background:#FFFFFF;
	border-radius:6px;
}
.blink {
	animation:allblink 2s infinite;
}
@keyframes allblink { 
	0% {color:#CC0000;background:#FFFFFF;}
	50% {color:#FFFFFF;background:#CC0000;} 
}
.lx-commands-tabs{
	margin-bottom:10px;
}
.lx-commands-tabs ul li{
	float:left;
	width:50%;
}
.lx-commands-tabs ul li a{
	display:block;
	padding:10px;
	font-weight:500;
	text-align:center;
	color:#242424;
	border:1px solid #DDDDDD;
}
.lx-commands-tabs ul li a.active{
	background:#EEEEEE;
}
.lx-phone-layouts{
	text-align:center;
}
.lx-phone-layouts img{
	display:inline-block !important;
	margin:10px !important;
}
.lx-commands-tabs{
	margin-bottom:10px;
}
.lx-commands-tabs ul li{
	float:left;
	width:50%;
}
.lx-commands-tabs ul li a{
	display:block;
	padding:10px;
	font-weight:500;
	text-align:center;
	color:#242424;
	border:1px solid #DDDDDD;
}
.lx-commands-tabs ul li a.active{
	background:#EEEEEE;
}
.lx-autocomplete-advanced{
	position:absolute;
	z-index:3;
	display:none;
	width:100%;
	max-height:300px;
	background:#FFFFFF;
	box-shadow:0px 0px 4px #BEBEBE;
	overflow:auto;
}
.lx-autocomplete-advanced span{
	padding:5px 10px;
	line-height:20px;
	border-bottom:1px solid #F2F2F2;
	cursor:pointer;
}
.lx-autocomplete-advanced span:hover{
	background:#F8F8F8;
}
.todropdown{
	display:none;
}
.todropdowninput{
	cursor:pointer;
}
#my-notification-button{
	position:absolute;
	top:-4px;
	left:120px;
	width:30px;
	height:30px;
	padding-top:8px;
	font-size:13px;
	font-weight:500;
	text-align:center;
	background:#d3d3d3;
	color:#242424;
	border-radius:50%;
}
.lx-message-reclamation{
	padding:15px;
}
.lx-message-reclamation p{
	margin-bottom:5px;
	padding:10px;
	background:#F8F8F8;
	color:#242424;
	border-radius:4px;
}
.lx-message-reclamation p:last-of-type{
	margin-bottom:0px;
}
.lx-message-reclamation p ins{
	display:block;
	font-style:italic;
	text-align:right;
	color:#828282;
}
#reader__camera_selection{
	display:block;
	margin:10px auto;
	padding:10px;
	border:1px solid #242424;
}
#reader__dashboard_section_swaplink{
	display:none !important;
}
#reader__dashboard_section_csr span button{
	display:-inline-block;
	padding:10px 20px;
	font-size:13px;
	font-weight:500;
	background:#338b8b;
	color:#FFFFFF;
	border-radius:8px;	
}
