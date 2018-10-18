var JSClass = (function () {
	function JSClass() {}

	return JSClass;
})();
JSClass.__extends = function (d, b) {
	function __() {
		this.constructor = d;
	}
	__.prototype = b.prototype;
	d.prototype = new __();
};
