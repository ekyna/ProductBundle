define(['gsap/TimelineLite'], function(TimelineLite) {

    var transition = .33,
        pause = 6;

    function animate($container) {
        var current = 0,
            busy = false,
            $slides = $container.find('.slide'),
            mainTL = new TimelineLite({
            onComplete: function() {
                this.restart()
            }
        });

        $slides.each(function(index) {
            var $slide = $(this),
                $img = $slide.find('> a'),
                $caption = $slide.find('> div'),
                position = 0,
                slideTL = new TimelineLite({
                    data: {index: index},
                    onStart: function() {
                        $slide.show();
                    },
                    onComplete: function() {
                        $slide.hide();
                        $container.trigger('product-slide-complete');
                    }
                });

            // Start animation
            slideTL.call(function() {
                current = this.data.index; busy = true
            }, null, slideTL, position);
            slideTL.from($img, transition, {left: 40, right: -40, opacity: 0}, position);
            slideTL.from($caption, transition, {left: -40, right: 40, opacity: 0}, position);
            position += transition;
            slideTL.call(function() {busy = false}, null, null, position);

            // Pause
            position += pause;

            // End animation
            slideTL.call(function() {
                busy = true
            }, null, slideTL, position);
            slideTL.to($img, transition, {left: -40, right: 40,opacity: 0}, position);
            slideTL.to($caption, transition, {left: 40, right: -40, opacity: 0}, position);
            position += transition;
            slideTL.call(function() {busy = false}, null, null, position);

            // Append to main timeline + labels
            mainTL
                .add(slideTL)
                .addLabel("start" + index, index * (2*transition + pause))
                .addLabel("end" + index, index * (2*transition + pause) + transition + pause);
        });

        function next() {
            if (busy) {
                return;
            }

            mainTL.seek("end" + current, true);
            current++;
            if (current > $slides.length - 1) {
                current = 0;
            }

            $container.one('product-slide-complete', function() {
                mainTL.seek("start" + current, true);
            });
        }

        function prev() {
            if (busy) {
                return;
            }

            mainTL.seek("end" + current);
            current--;
            if (0 > current) {
                current = $slides.length - 1;
            }

            $container.one('product-slide-complete', function() {
                mainTL.seek("start" + current, true);
            });
        }

        $container.on('click', 'a.prev-slide', prev);
        $container.on('click', 'a.next-slide', next);

        mainTL.seek(transition);
    }

    return {
        init: function($element) {
            $element.each(function() {
                animate($(this));
            });
        }
    }
});