"use strict";
// -----------------------
// Tomatoro.js
// -----------------------
var PHI = (1+ Math.sqrt(5))/2;// gold

// Colors
var BLACK = "#000000";
var GREY  = 'rgba(32,250,250)';
var RED = 'rgba(250,50,0,1)';//#FF3200
var WHITE = 'rgba(250,250,250,1)';
var RED = '#FF3200';
var RED1 = '#FF6540';
var RED2 = '#FF9980';
var RED3 = '#FFCCBF';
var BLUE = '#00ccff';

$(window).load(function(){	
	var tomatoro = new TomatoroManager();
	
	document.addEventListener("keydown",function(e){
		if(typeof e.key === 'undefined' || e.key.lastIndexOf("F", 0) === 0)
			return;
		
		if (e.key === "Backspace" || e.key === "Enter" || e.key === "Del" || e.key === "Delete" || e.key === "."){
			e.preventDefault();
			tomatoro.push_character(e.key);
			//tomatoro.status = e.key + " " +e.keyCode + e.charCode;
		}
	});
	
	document.addEventListener("keypress",function(e){
		var key = (typeof e.key !== 'undefined') ? e.key : (e.which !== 0) ? String.fromCharCode(e.which) : e.keyCode;
		//write name
		var code = (e.which !== 0) ? e.which : e.keyCode;
		switch (code){//e.which) {
			case 8  : key = "Backspace";break;
			case 13 : key = "Enter";break;
			case 9  : key = "Tab";break;
			case 27 : key = "Esc";break;
			case 18 : key = "Alt";break;
			case 17 : key = "Control";break;
			case 16 : key = "Shift";break;
			case 46 : key = (e.keyCode === 0) ? "." : "Del"; break;
			default : break;
		}
		tomatoro.push_character(key);
		//tomatoro.status = "key : " + e.key +" "+ e.keyCode +" "+ e.charCode;
		e.preventDefault();
		return false;
	});
	
	$("#canvases").mousedown(function(e){
		tomatoro.startstop();
	});
	
	// redraws if window is resized        
	$(window).resize(function() {
		tomatoro.init();
	});
});

// -----------------------
// Completed Tomato object
// -----------------------
function CompletedTomato(date,name){
	this.date =  date;
	this.name =  name;
}

// -----------------------
// Tomatoro Manager object
// -----------------------
function TomatoroManager() {
	var self = this;
	
	this.TOMATORO_PERIOD = 1500;//1500;// 25 min = 20 * 60s = 1500s
	this.START_MESSAGE = "OFF";
	this.CANCEL_MESSAGE = "ON";
	this.STATE = 0; // 0 = initial state, 1 = on/play, 2 = off/paused
	
	this.start_time = null;
	this.current_time = null;
	this.current_seconds = 0;
	this.total_seconds = 0;
	this.task_name = "";
	this.status = "";
	
	this.completed_tomatoes = [];
	
	this.timer_height = 0;
	
	this.FONT = "Helvetica";
	this.fat_size = 216;
	this.big_size = (self.fat_size / PHI);
	this.FONT_MEDIUM = 'bold 24px"'+ self.FONT +'"';
	this.FONT_BIG   = 'bold '+ self.big_size + 'px "'+ self.FONT +'"';
	this.FONT_FAT = 'bold '+ self.fat_size + 'px "'+ self.FONT +'"';
	
	// sounds
	this.snd = new Audio("app/tomatoro/public/sounds/kumkum1.mp3"); // buffers automatically when created
	
	// loops
	this.loop1 = null;
	this.loop2 = null;
	this.loop3 = null;
	
	// layers / canvases 
	this.layer1 = document.getElementById("layer1");
	this.ctx1 = layer1.getContext("2d");
	this.layer2 = document.getElementById("layer2");
	this.ctx2 = layer2.getContext("2d");
	
	// chart
	this.chart = null;

	// timeline
	this.timeline = null;

	this.init = function(){
		self.ctx1.canvas.width  = window.innerWidth;
		self.ctx1.canvas.height = window.innerHeight;
		self.ctx2.canvas.width  = window.innerWidth;
		self.ctx2.canvas.height = window.innerHeight;

		// background
		self.ctx1.fillStyle = RED;
		
		// timer / clock
		self.ctx2.fillStyle = WHITE;
		self.ctx2.font = self.FONT_FAT;
		self.ctx2.textAlign = 'center';
		self.timer_height = self.layer1.height/PHI;
		
		//TMP
		//clear_history();

		// timeline
		init_timeline();
		//init_bar_chart();

		// sound
		self.snd.load();
		
		// Loops
		draw_background(); //self.loop3 = setInterval(draw_background, 400);
		self.loop1 = setInterval(update_clock, 20);
		self.loop2 = setInterval(draw_tomatoro, 40);
	}

	var init_timeline = function(){
		self.current_time = seconds_to_time(0);

		var timeline_container_height = (self.layer1.height - self.timer_height),
			timeline_width = Math.floor(self.layer1.width / PHI),
			timeline_height = Math.floor(timeline_container_height / PHI),
			posx = Math.floor(self.ctx2.canvas.width / 2 - timeline_width / 2),
			posy = Math.floor(self.timer_height + (timeline_container_height - timeline_height )/ 2);
		if (self.timeline !== null){
			self.timeline.posx = posx;
			self.timeline.posy = posy;
			self.timeline.width = timeline_width;
			self.timeline.height = timeline_height;
			self.timeline.resize();
		}
		else{
			var past_midnight = new Date();
			past_midnight.setHours(0,0,0,0);
			
			var next_midnight = new Date();
			next_midnight.setHours(24,0,0,0);

			var start_value = past_midnight,
				end_value = next_midnight;
			
			self.timeline = new Timeline(self.ctx2,posx,posy,timeline_width,timeline_height,"Tomatoro Timeline","t","",start_value,end_value,null);
		}

		load_slices();
		load_state();
	}

	var init_bar_chart = function(){
		self.current_time = seconds_to_time(self.TOMATORO_PERIOD);

		var timeline_height = (self.layer1.height - self.timer_height);
		var chart_width = Math.floor(self.layer1.width / PHI);
		var chart_height = Math.floor(timeline_height / PHI);
		var posx = Math.floor(self.ctx2.canvas.width / 2 - chart_width / 2);
		var posy = Math.floor(self.timer_height + (timeline_height - chart_height )/ 2);
		if (self.chart !== null){
			self.chart.posx = posx;
			self.chart.posy = posy;
			self.chart.width = chart_width;
			self.chart.height = chart_height;
			self.chart.resize();
		}
		else
			self.chart = new Chart(self.ctx2,posx,posy,chart_width,chart_height,"Tomatoro Timeline","t","n",null);

		load_tomatoes();
	}
	
	this.startstop = function(){
		if(self.STATE === 1)
			self.stop_clock();
		else
			self.start_clock();
		draw_background();
	}
	
	this.start_clock = function(){
		self.start_time = new Date;
		self.STATE = 1;
		update_clock();
		save_state();
		// stop sound
		self.snd.pause();
		self.snd.load();
	}
	
	this.stop_clock = function(){
		self.STATE = 2;
		self.total_seconds += self.current_seconds;
		save_slice();
		save_state();
	}
	
	this.push_character = function(key){
		if (key === "Backspace")
			self.task_name = (self.task_name.length > 0) ? self.task_name.slice(0, -1) : self.task_name;
		else if (key === "Alt" || key === "Control" || key === "Shift" || key === "Tab" || key === "Esc") //TODO : More keys !
			return;
		else if (key === "Enter")
			self.startstop();
		else if (key === "Spacebar")
			self.task_name += " ";
		else if (key === "Del" || key === "Delete")
			clear_history();
		else
			self.task_name += key;
	}
	
	// ---- UPDATE ----
	
	var update_clock = function(){
		if(self.STATE === 1){
			self.current_seconds = Math.round((new Date() - self.start_time) / 1000);//self.TOMATORO_PERIOD - Math.round((new Date() - self.start_time) / 1000);
			self.current_time = seconds_to_time(self.current_seconds);
		}
	}
	
	// ---- DRAW ---- 

	var get_main_color1 = function(){
		return (self.STATE === 1)
			? RED 
			: BLACK;
	}

	var get_main_color = function(){
		return (self.STATE === 1)
			? BLUE 
			: BLACK;
	}

	var get_main_color2 = function(){
		return (self.STATE === 1)
			? BLACK
			: WHITE;
	}
	
	var draw_background = function(){
		// timer
		self.ctx1.fillStyle = get_main_color2();
		self.ctx1.fillRect(0,0,self.layer1.width,self.timer_height);
		// timeline 
		self.ctx1.fillStyle = get_main_color1();
		self.ctx1.fillRect(0,self.timer_height,self.layer1.width,self.layer1.height);
	}
	
	var draw_tomatoro = function(){
		draw_clock();
		draw_timeline();
		/*draw_chart()*/
		draw_status();
	}
	
	var draw_clock = function(){
		self.ctx2.clearRect(0,0,self.layer1.width,self.layer1.height);
		self.ctx2.fillStyle = get_main_color();
		var x = self.ctx2.canvas.width / 2;
		
		// name
		if (self.task_name !== ""){
			self.ctx2.font = self.FONT_MEDIUM;
			var y = Math.floor(self.layer1.height / (11*PHI));
			self.ctx2.fillText(self.task_name, x, y);
		}
		
		// ON OFF
		self.ctx2.fillStyle = get_main_color();
		self.ctx2.font = self.FONT_FAT;
		var x = self.ctx2.canvas.width / 2,
			y = Math.floor(self.layer1.height / (2*PHI)),
			message = (self.STATE === 2 || self.STATE === 0) ? self.START_MESSAGE : self.CANCEL_MESSAGE;
		self.ctx2.fillText(message, x, y);

		// ---- times
		self.ctx2.font = self.FONT_MEDIUM;
		var y = Math.floor(self.layer1.height / (2*PHI));
		// current time
		x = self.ctx2.canvas.width / 2;
		self.ctx2.fillText("slice: " + self.current_time, x, y + 108);
		// total time
		self.ctx2.fillText("today: " + seconds_to_time(self.total_seconds), x, y + 148);
	}
	
	var draw_timeline = function(){
		self.timeline.draw();
	}

	var draw_chart = function(){
		self.chart.draw();
	}
	
	var draw_status = function(){
		self.ctx2.fillStyle = get_main_color();
		self.ctx2.font = self.FONT_MEDIUM;
		var x = self.ctx2.canvas.width / 2,
			y = 24;
		self.ctx2.fillText(self.status, x, y);
	}
	
	// ---- Helpers
	
	var add_leading_zero = function(number){
		return (number > 9) ? number : "0" + number;
	}
	
	var seconds_to_time = function(time_in_seconds){
		var hours = add_leading_zero(Math.floor(time_in_seconds / 3600)),
			minutes = add_leading_zero(Math.floor(time_in_seconds / 60) % 60),
			seconds = add_leading_zero(time_in_seconds % 60);
		return hours + " : " + minutes + " : " + seconds;	
	}
	
	var get_formated_date = function (date) {
		var day = date.getDate(),
			month = date.getMonth() + 1;
		return month + '/' + day;
	}

	// ---- TIMELINE

	var save_slice = function(){
		self.timeline.add_slice(new Slice(self.start_time, new Date(), self.task_name));
		localStorage["slices"] = JSON.stringify(self.timeline.slices);
	}
	
	var load_slices = function(){
		if(localStorage["slices"] != undefined){
			self.timeline.slices = JSON.parse(localStorage["slices"]);
			var slice = null;
			for (var i = 0; i < self.timeline.slices.length; i++) {
				slice = self.timeline.slices[i];
				slice.start_value = new Date(slice.start_value);
				slice.end_value = new Date(slice.end_value);
			}
			self.timeline.init();
			self.timeline.draw();
		}
	}

	var save_state = function(){
		localStorage["start_time"] = JSON.stringify(self.start_time);
		localStorage["state"] = JSON.stringify(self.STATE);
		localStorage["total_seconds"] = JSON.stringify(self.total_seconds);
	}

	var load_state = function(){
		self.start_time = (localStorage["start_time"] != undefined) ? JSON.parse(localStorage["start_time"]) : null;
		self.start_time = new Date(self.start_time);
		self.STATE = (localStorage["state"] != undefined) ? parseInt(JSON.parse(localStorage["state"])) : null;
		self.total_seconds = (localStorage["total_seconds"] != undefined) ? parseInt(JSON.parse(localStorage["total_seconds"])) : null;
	}

	// ---- BAR CHART 

	var save_tomato = function (){
		var completed_tomato = new CompletedTomato(get_formated_date(new Date()),self.task_name);
		self.completed_tomatoes.push(completed_tomato);
		self.chart.add_chart_value(new ChartValue(completed_tomato.date,completed_tomato.name, 1));
		
		bake_cookie("tomatoes",self.completed_tomatoes);
	}

	var load_tomatoes = function(){
		self.completed_tomatoes = read_cookie("tomatoes");
		if (typeof self.completed_tomatoes !== 'undefined' && self.completed_tomatoes !== null){
			self.chart.clear();
			var completed_tomato = null;
			for (var i = 0; i < self.completed_tomatoes.length; i++) {
				completed_tomato = self.completed_tomatoes[i];
				self.chart.add_chart_value(new ChartValue(completed_tomato.date,completed_tomato.name, 1));
			}	
		}
		else{
			self.completed_tomatoes = [];
		}
	}
	
	var clear_history = function(){
		alert("clear history");
		localStorage.clear();
		self.timeline.clear();
		load_state();

		//delete_cookie("tomatoes");
		//load_tomatoes();
		//self.chart.clear();
	}
	
	// initialize !!
	this.init();
}
