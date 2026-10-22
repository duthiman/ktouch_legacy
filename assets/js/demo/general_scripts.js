var handleSelectpicker = function() {
     $(".selectpicker").select2(); 
};


    
var General = function () {
	"use strict";
    return {
        //main function
        init: function () {
			 
			handleSelectpicker();
			 
        }
    };
}();