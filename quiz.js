
function PathQuiz(jq, data, url) {
    this.dataURL = url;
    this.el = jQuery(jq).addClass("ao-path-quiz");
    this.data = data;
    if (url) {
	jQuery.getJSON(url,(function(data,status,xhr){
		    this.data = data;
		    this.displayNext(this.data.start);
		}).bind(this));
    } else if (this.data) {
	this.displayNext(this.data.start);
    }
};

var funcs = {
    displayNext: function(num) {
	jQuery("input.ao-path-quiz-radio").prop("disabled",true);
	jQuery("fieldset.ao-path-quiz-active").removeClass("ao-path-quiz-active");
	var q = jQuery("<fieldset>").addClass("ao-path-quiz-active");
	var title = jQuery("<legend>").appendTo(q);
	if (num < 100) {
	    title.text(this.data.questions[num].text);
	    for (i in this.data.questions[num].choices) {
		if (i !== "length") {
		    var n = this.data.questions[num].choices[i];
		    var next = this.data.answers[n].next;
		    var container = jQuery("<div>").appendTo(q);
		    var button = jQuery("<input>").prop("type","radio")
			.addClass("ao-path-quiz-radio")
			.prop("name","path-quiz-question-" + num)
			.prop("id","path-quiz-question-" + num + i)
			.prop("value",n).text(next).appendTo(container)
			.click(this.displayNext.bind(this,next));
		    var label = jQuery("<label>").prop("for","path-quiz-question-" + num + i)
			.text(this.data.answers[n].text).appendTo(container);
		}
	    }
	} else {
	    var res = this.data.results[num];
	    title.text("You are: " + res.name);
	    jQuery("<p>").text(res.blurb).appendTo(q);
	}
	q.appendTo(this.el);
	q.slideDown();
    }
};

PathQuiz.prototype = funcs;
window.AOPathQuiz = window.AOPathQuiz || PathQuiz;
