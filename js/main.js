/* GLOBAL */
var map;
var el={};
var controlDiv;
/* CONST */
var today = new Date();
var popup = L.popup();
var colours = ['#fff5f0','#fcbba1','#fb6a4a','#cb181d','#00000d'];
var colour_checked="#0F0";
var colour_notchecked="#000";

$.i18n.init({resGetPath: 'locales/__lng__.json' },function(e){initMe()});


function buildControls(){
	/* GET DATA CONTROL */
	var GetDataControl = L.Control.extend({options: { position: 'topright'},
		onAdd: function (map) {
			L.DomEvent.on(controlDiv, 'mousedown', L.DomEvent.stopPropagation)
			.on(controlDiv, 'dblclick', L.DomEvent.stopPropagation)
			.on(controlDiv, 'touchstart', L.DomEvent.stopPropagation)
			.addListener(controlDiv, 'click', function () { getData(); });
			L.DomEvent.disableClickPropagation(controlDiv);
			controlDiv.innerHTML="&#x2b07;";
			return controlDiv;
		}});
	map.addControl(new GetDataControl());
	/* LEGEND CONTROL */
	var MyLegend = L.Control.extend({options: { position: 'bottomleft'},
		onAdd: function (map) {
			var controlDiv = L.DomUtil.create('div', 'leaflet-control-own leaflet-control-legend');
			var char="&#x2b24;";
			controlDiv.innerHTML="<span style='color:"+colours[0]+"'>"+char+"</span> < "+$.t("day",{count : 30})+"<br/>"+
				"<span style='color:"+colours[1]+"'>"+char+"</span> < "+$.t("year",{count : 1})+"<br/>"+
				"<span style='color:"+colours[2]+"'>"+char+"</span> < "+$.t("year",{count : 2})+"<br/>"+
				"<span style='color:"+colours[3]+"'>"+char+"</span> < "+$.t("year",{count : 5})+"<br/>"+
				"<span style='color:"+colours[4]+"'>"+char+"</span> > "+$.t("year",{count : 5})+"<br/>";
			char="&#x25ef;";
			controlDiv.innerHTML+="<span style='color:"+colour_checked+"'>"+char+"</span> "+$.t("checked")+"<br/>"+
				"<span style='color:"+colour_notchecked+"'>"+char+"</span> "+$.t("not checked")+"<br/>";
			return controlDiv;
		}});
	map.addControl(new MyLegend());
	/* Description */
	var MyDesc = L.Control.extend({options: { position: 'bottomright'},
		onAdd: function (map) {
			var controlDiv = L.DomUtil.create('div', 'leaflet-control-own leaflet-control-desc');
			controlDiv.innerHTML="";
			var _this=this;
			L.DomEvent.addListener(controlDiv, 'click', function(){map.removeControl(_this);});
			controlDiv.innerHTML+=$.t("Click to close.")+"<br/>"+$.t("To download data, use the button in the top right corner.")+"<br/>"+
				$.t("If you can't find POI, try to zoom in and reload.");
			return controlDiv;
		}});
	map.addControl(new MyDesc());
}

function getData(){
	if(controlDiv.innerHTML == "...")return;
	$.getJSON( "getData.php?minlat="+map.getBounds().getSouth()+"&minlon="+map.getBounds().getWest()+"&maxlat="+map.getBounds().getNorth()+"&maxlon="+map.getBounds().getEast(), function( data ) {
		addData(data);
		controlDiv.innerHTML = "&#x2b07;";
	});
	controlDiv.innerHTML = "...";
}

function diffDays(d1, d2) {
	var t2 = d2.getTime();
	var t1 = d1.getTime();
	return parseInt( ( t2 - t1 ) / ( 24 * 3600 * 1000 ) );
}

function checkOld( a ){
	var newest = new Date( a.timestamp );
	if( a.ex != null ){
		for(var i = 0; i < a.ex.length;i++){
			var ww = a.ex[i].date.split(" ");
			ww = ww[0].split("-");
			var p = new Date( ww[0], ww[1] - 1, ww[2] );
			if( diffDays( newest, p ) > 0){
				newest = p;
			}
		}
	}
	return diffDays( newest , today );
}

function onMarkerClick( e ){
	var w = el[e.target["elidx"]];
	var text = "ID:";
	if(w.type == "way")
		text += "<a href='http://openstreetmap.org/browse/way/"+w.id+"' target='_blank'>";
	else
		text += "<a href='http://openstreetmap.org/browse/node/"+w.id+"' target='_blank'>";
	text += w.id;
	text += "</a> Type: " + w.type +
		" <a href='http://level0.osmz.ru/?url=" + w.type + "/" + w.id + "' target='_blank'>Level0</a>" +
		"<br/><hr/>Tags:<br/>";
	for(var i in w.tags){
		text += "<b>" + i + "</b>  " + w.tags[i] + "<br/>";
	}
	text += "<hr/>Last editor:<br/>";
	text += "<a href='http://openstreetmap.org/user/" + encodeURIComponent(w.user) + "' target='_blank'><b>" + w.user + "</b></a>" + w.timestamp + "<br/>";
	text += "<hr/>Checks:<br/>";
	if(w.ex != null){
		for(var i = 0;i < w.ex.length;++i){
			text += w.ex[i].date + " <b>" + w.ex[i].username + "</b><br/>";
		}
	}
	text += "<a onClick=\"checked('" + w.id + "','" + w.type + "')\"><b> This is correct. </b></a>";
	text += "<hr/>";
	//BTC POWER!
	text += '<iframe scrolling="no" style="border: 0; width: 234px; height: 60px;" src="//coinurl.com/get.php?id=30146"></iframe>';
	text += "<hr/>";
	popup.setLatLng(e.latlng).setContent(text).openOn(map);
}

function addData(data){
	var i;
	for (i = 0; i < data.elements.length; ++i) {
		var a = data.elements[i];
		var idd = a["type"]+a["id"];
		if( el.hasOwnProperty(idd) ){
			map.removeLayer( el[idd].marker );
			delete el[idd];
		}
		el[idd] = a;
		if( a["type"] != "node" ){
			el[idd]["lat"] = a.center["lat"];
			el[idd]["lon"] = a.center["lon"];
		}
		var days = checkOld(a);
		var rad = 5;
		//Colour
		var colorA = colours[0];
		if(days>1825)
			colorA = colours[4];
		else if(days>730)
			colorA = colours[3];
		else if(days>365)
			colorA = colours[2];
		else if(days>30)
			colorA = colours[1];
		//Size
		if(days>365)
			rad=10;

		var c2=colour_notchecked;
		if(el[idd]["ex"]!=null)
			c2=colour_checked;

		el[idd]["marker"]=L.circleMarker([a["lat"], a["lon"]],{radius: rad,color:c2,fillColor:colorA,fillOpacity:0.9}); 
		el[idd]["marker"].addTo(map);
		el[idd]["marker"].on('click', onMarkerClick);
		el[idd]["marker"]["elidx"]=idd;
	}
}

var in_prog=[];

function checked(id,type){
	if( in_prog.hasOwnProperty(id) ){
		console.log("In progress...");
		return;
	}
	in_prog[id]=true;
	$.get("addData.php?osmid="+id+"&type="+type, function( data ) {
		delete in_prog[id];
		console.log(data);
		if(data=="OK"){
			map.removeLayer(el[type+id].marker);
			delete el[type+id];
			map.closePopup();
		}else if(data=="AUTHERR"){
			window.open("http://unexpired.osm24.eu/auth.php",'_blank');
		}
	});
}

function initMe() {
	map = L.map('map').setView([51.505, -0.09], 13);
	controlDiv = L.DomUtil.create('div', 'leaflet-control-command');
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
			'Tiles: <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		}).addTo(map);
	buildControls();
	var hash = new L.Hash(map);
}