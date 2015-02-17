/**
 preventOverScroll.js

 Prevents iOS 5.0 Safari scrolling elements from causing the page itself
 to scroll.

 Permission granted to use this code under the Open Source MIT License.
 http://www.opensource.org/licenses/mit-license.php
Copyright (c) 2012 Morgan McGuire
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


 morgan@casual-effects.com
 http://casual-effects.com
*/


/** Invoke on a DIV that is set to touchscroll by the CSS properties:
    
      -webkit-overflow-scrolling: touch;
      overflow: scroll;

    iOS Safari has a quirk that dragging a scrolling element upwards
    past its top or downwards past its bottom will cause the entire
    page to scroll, including even position: fixed elements.  This
    only happens if the scroll starts at the limit; if the element
    begins the scrolling process somewhere in the middle, scrolling
    past the top or bottom does not affect the page.  This does not
    happen at all for horizontal scrolling.
    
    To prevent this behavior, we detect when a touch on a scrolling
    element begins at the limit and disable scrolling in that
    direction specifically.
    
    The containing element must disable default behavior during the
    bubble phase of touchmove delivery, e.g.,
    
      document.addEventListener('touchmove', function(e) { e.preventDefault(); }, false);
    
    Warning: This may have issues with multiple touches; I'm assuming
    that the touch seen in touchmove was the same as the one in
    touchstart.  However, once the second touch begins, we're not in
    single-finger scrolling, so maybe it doesn't matter.  
*/
function preventOverScroll(scrollPane) {
    // See http://www.quirksmode.org/js/events_order.html
    var CAPTURE_PHASE = true;  // happens first, outside to inside
    var BUBBLE_PHASE  = false; // happens second, inside to outside

    // These variables will be captured by the closures below
    var allowScrollUp = true, allowScrollDown = true, lastY = 0;

    scrollPane.addEventListener
    ('touchstart',
     function(e) { 
         
         // See http://www.w3.org/TR/cssom-view/#dom-element-scrolltop
         allowScrollUp = (this.scrollTop > 0);
         allowScrollDown = (this.scrollTop < this.scrollHeight - this.clientHeight);
         
         // Remember where the touch started
         lastY = e.pageY;
     }, 
     CAPTURE_PHASE);

    // If the touch is on the scroll pane, don't let it get to the
    // body object which will cancel it
    scrollPane.addEventListener
    ('touchmove', 
     function (e) {
         var up   = (e.pageY > lastY);
         var down = ! up;
         lastY    = event.pageY;
         
         // Trying to start past scroller bounds
         if ((up && allowScrollUp) || (down && allowScrollDown)) {
             // Stop this event from propagating, lest 
             // another object cancel it.
             e.stopPropagation(); 
         } else {
             // Cancel this event
             e.preventDefault();
         }
     }, 
     CAPTURE_PHASE);
};
