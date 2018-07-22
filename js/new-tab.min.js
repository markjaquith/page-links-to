(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

(function (d) {
	// Makes an anchor element open in a new tab.
	var newTab = function newTab(el) {
		var newTabRegex = /#new_tab$/;
		if (el.tagName === 'A' && newTabRegex.test(el.getAttribute('href'))) {
			el.setAttribute('target', '_blank');
			el.setAttribute('href', el.getAttribute('href').replace(newTabRegex, ''));
		}
	};

	// Immediately attach a click handler.
	d.addEventListener('click', function (e) {
		return newTab(e.target);
	});

	// On page load, convert any existing new tab links.
	d.addEventListener('DOMContentLoaded', function () {
		var anchors = d.getElementsByTagName('A');
		for (var i = 0; i < anchors.length; i++) {
			newTab(anchors[i]);
		}
	});
})(document);

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9uZXctdGFiLmpzeCJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUEsQ0FBQyxhQUFLO0FBQ0w7QUFDQSxLQUFNLFNBQVMsU0FBVCxNQUFTLEtBQU07QUFDcEIsTUFBTSxjQUFjLFdBQXBCO0FBQ0EsTUFBSSxHQUFHLE9BQUgsS0FBZSxHQUFmLElBQXNCLFlBQVksSUFBWixDQUFpQixHQUFHLFlBQUgsQ0FBZ0IsTUFBaEIsQ0FBakIsQ0FBMUIsRUFBcUU7QUFDcEUsTUFBRyxZQUFILENBQWdCLFFBQWhCLEVBQTBCLFFBQTFCO0FBQ0EsTUFBRyxZQUFILENBQWdCLE1BQWhCLEVBQXdCLEdBQUcsWUFBSCxDQUFnQixNQUFoQixFQUF3QixPQUF4QixDQUFnQyxXQUFoQyxFQUE2QyxFQUE3QyxDQUF4QjtBQUNBO0FBQ0QsRUFORDs7QUFRQTtBQUNBLEdBQUUsZ0JBQUYsQ0FBbUIsT0FBbkIsRUFBNEI7QUFBQSxTQUFLLE9BQU8sRUFBRSxNQUFULENBQUw7QUFBQSxFQUE1Qjs7QUFFQTtBQUNBLEdBQUUsZ0JBQUYsQ0FBbUIsa0JBQW5CLEVBQXVDLFlBQU07QUFDNUMsTUFBTSxVQUFVLEVBQUUsb0JBQUYsQ0FBdUIsR0FBdkIsQ0FBaEI7QUFDQSxPQUFLLElBQUksSUFBSSxDQUFiLEVBQWdCLElBQUksUUFBUSxNQUE1QixFQUFvQyxHQUFwQyxFQUF5QztBQUN4QyxVQUFPLFFBQVEsQ0FBUixDQUFQO0FBQ0E7QUFDRCxFQUxEO0FBTUEsQ0FwQkQsRUFvQkcsUUFwQkgiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIoZCA9PiB7XG5cdC8vIE1ha2VzIGFuIGFuY2hvciBlbGVtZW50IG9wZW4gaW4gYSBuZXcgdGFiLlxuXHRjb25zdCBuZXdUYWIgPSBlbCA9PiB7XG5cdFx0Y29uc3QgbmV3VGFiUmVnZXggPSAvI25ld190YWIkLztcblx0XHRpZiAoZWwudGFnTmFtZSA9PT0gJ0EnICYmIG5ld1RhYlJlZ2V4LnRlc3QoZWwuZ2V0QXR0cmlidXRlKCdocmVmJykpKSB7XG5cdFx0XHRlbC5zZXRBdHRyaWJ1dGUoJ3RhcmdldCcsICdfYmxhbmsnKTtcblx0XHRcdGVsLnNldEF0dHJpYnV0ZSgnaHJlZicsIGVsLmdldEF0dHJpYnV0ZSgnaHJlZicpLnJlcGxhY2UobmV3VGFiUmVnZXgsICcnKSk7XG5cdFx0fVxuXHR9O1xuXG5cdC8vIEltbWVkaWF0ZWx5IGF0dGFjaCBhIGNsaWNrIGhhbmRsZXIuXG5cdGQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBlID0+IG5ld1RhYihlLnRhcmdldCkpO1xuXG5cdC8vIE9uIHBhZ2UgbG9hZCwgY29udmVydCBhbnkgZXhpc3RpbmcgbmV3IHRhYiBsaW5rcy5cblx0ZC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGNvbnN0IGFuY2hvcnMgPSBkLmdldEVsZW1lbnRzQnlUYWdOYW1lKCdBJyk7XG5cdFx0Zm9yIChsZXQgaSA9IDA7IGkgPCBhbmNob3JzLmxlbmd0aDsgaSsrKSB7XG5cdFx0XHRuZXdUYWIoYW5jaG9yc1tpXSk7XG5cdFx0fVxuXHR9KTtcbn0pKGRvY3VtZW50KTtcbiJdfQ==
