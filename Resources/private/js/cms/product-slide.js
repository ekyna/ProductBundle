define(['gsap/TimelineLite'], function (TimelineLite) {

    var resizeInitialized = false;

    function setFontSize($container) {
        var $wrap = $container.find('> .wrap'),
            size = Math.floor($wrap.width() * 0.045),
            height = Math.floor($wrap.width() * 0.055);

        if (12 > size) {
            size = 12;
            height = 15;
        } else if(24 < size) {
            size = 24;
            height = 30;
        }

        $wrap.find('.slide-caption').css({'fontSize': size + 'px', 'lineHeight': height + 'px'});
    }

    function animate ($container) {
        var transition = .30,
            offset = .15,
            pause = 6,
            current = 0,
            busy = false,
            $slides = $container.find('.slide'),
            mainTL = new TimelineLite({
                onComplete: function () {
                    this.restart();
                }
            });

        pause = $container.find('.slides').data('duration');
        if (pause > 1000) {
            pause /= 1000;
        } else {
            pause = 4;
        }

        $slides.each(function (index) {
            var $slide = $(this),
                $img = $slide.find('.slide-image'),
                $caption = $slide.find('.slide-caption'),
                $button = $slide.find('.slide-button'),
                position = 0,
                slideTL = new TimelineLite({
                    data: {index: index},
                    onStart: function () {
                        $slide.show();
                    },
                    onComplete: function () {
                        $slide.hide();
                        $container.trigger('product-slide-complete');
                    }
                });

            // Start animation
            slideTL.call(function () {
                current = this.data.index;
                busy = true;
            }, null, slideTL, position);
            slideTL.from($img, transition, {left: 40, right: -40, opacity: 0}, position);
            slideTL.from($caption, transition, {left: -40, right: 40, opacity: 0}, position + offset);
            slideTL.from($button, transition, {scale: .5, opacity: 0}, position + 2 * offset);
            position += transition + 2 * offset;
            slideTL.call(function () {
                busy = false;
            }, null, null, position);

            // Pause
            position += pause;

            // End animation
            slideTL.call(function () {
                busy = true;
            }, null, slideTL, position);
            slideTL.to($button, transition, {scale: .5, opacity: 0}, position);
            slideTL.to($caption, transition, {left: 40, right: -40, opacity: 0}, position + offset);
            slideTL.to($img, transition, {left: -40, right: 40, opacity: 0}, position + 2 * offset);
            position += transition + 2 * offset;
            slideTL.call(function () {
                busy = false;
            }, null, null, position);

            // Append to main timeline + labels
            mainTL
                .add(slideTL)
                .addLabel("start" + index, index * (2 * transition + 4 * offset + pause))
                .addLabel("end" + index, index * (2 * transition + 4 * offset + pause) + transition + 2 * offset + pause);
        });

        function next () {
            if (busy) {
                return;
            }

            mainTL.seek("end" + current, true);
            current++;
            if (current > $slides.length - 1) {
                current = 0;
            }

            $container.one('product-slide-complete', function () {
                mainTL.seek("start" + current, true);
            });
        }

        function prev () {
            if (busy) {
                return;
            }

            mainTL.seek("end" + current);
            current--;
            if (0 > current) {
                current = $slides.length - 1;
            }

            $container.one('product-slide-complete', function () {
                mainTL.seek("start" + current, true);
            });
        }

        function fontSize () {

        }

        $container.on('click', 'a.prev-slide', prev);
        $container.on('click', 'a.next-slide', next);


        mainTL.seek(transition + 2 * offset);
    }

    return {
        init: function ($element) {
            $element.each(function () {
                var $container = $(this);
                animate($container);
                setFontSize($container);
            });

            if (0 < $element.length && !resizeInitialized) {
                var timeout = null,
                    onResize = function () {
                        $('.product-slide').each(function () {
                            setFontSize($(this));
                        });
                    };

                $(window).on('resize', function () {
                    if (timeout) {
                        clearTimeout(timeout);
                    }
                    timeout = setTimeout(onResize, 300);
                });

                resizeInitialized = true;
            }
        }
    };
});