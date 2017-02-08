/*
 * Mini progress bar
 * 
 * @license
 * Copyright (c) 2016 Grigory Ponomar.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */


MiniProgressBar = function() {
	this.transition = 300;
	this.count = 0;
	this.state = 'idle';
	this.bar = $('<div class="loading-bar"><div></div></div>');
	$(document.body).prepend(this.bar);
}

MiniProgressBar.prototype.start = function() {
	var that = this;
	if (this.count == 0) {
		this.bar.removeClass('loading-finish loading-progress').addClass('loading-start');
		this.state = 'start';
		setTimeout(function() {
			if (that.state == 'start') {
				that.bar.removeClass('loading-start').addClass('loading-progress');
				that.state = 'progress';
			}
		}, 50);
	}
	this.count++;
}

MiniProgressBar.prototype.finish = function() {
	var that = this;
	this.count--;
	if (this.count <= 0) {
		this.bar.removeClass('loading-start loading-progress').addClass('loading-finish');
		this.state = 'finish';
		setTimeout(function() {
			if (that.state == 'finish') {
				that.bar.removeClass('loading-finish');
				that.state = 'idle';
			}
		}, this.transition);
	}
}
