var anSliderProducts = function() {

    this.j = jQuery;
    this.dom = null;
    this.stSliderId = null;

    this.init = function(stSliderId, boIsLazyLoad) {

        this.stSliderId = stSliderId;
        if (!this.stSliderId)
            return false;

        //console.log('AnSliderProducts: init', this.stSliderId);

        this.dom = this.j('#'+this.stSliderId);
        if (!this.dom || !this.dom.length)
            return false;

        this.onAdaptiveHeight();
        this.j(window).resize(this.j.proxy(this.onAdaptiveHeight, this));

        Module.load('slick', this.j.proxy(function(e, stModuleName, boIsLoaded) {

            var domSlick = this.dom.find('.products-slider_items');

            if (domSlick.prev().hasClass('products-slider_link-all'))
                domSlick.prev().before('<div class="products-slider_arrows"></div>');
            else
                domSlick.before('<div class="products-slider_arrows"></div>');

            domSlick.slick({
                dots: false,
                infinite: true,
                centerMode: false,
                slidesToShow: 5,
                slidesToScroll: 1,
                swipeToSlide: true,

                //variableWidth: true,

                appendArrows: this.dom.find('.products-slider_arrows'),
                nextArrow: '<i class="slick-next"></i>',
                prevArrow: '<i class="slick-prev"></i>',

                responsive: [/*{
						breakpoint: 1420,
						settings: {
							slidesToShow: 4,

						}
					},*/ {
                    breakpoint: 1280,
                    settings: {
                        slidesToShow: 4,

                    }
                }, {
                    breakpoint: 920,
                    settings: {
                        slidesToShow: 3,

                    }
                }, {
                    breakpoint: 580,
                    settings: {
                        slidesToShow: 2,

                    }
                },
                ]
            });

            if (boIsLazyLoad && typeof LazyLoad == 'function')
                this.j('img[data-src]').lazyload({
                    effect: 'fadeIn'
                });

            this.onAdaptiveHeight();

        }, this));

    };

    this.isHome = function() {

        return this.j('body').hasClass('_home');

    };

    this.onAdaptiveHeight = function(e) {

        if (!this.dom || !this.dom.length)
            return false;

        var dom = this.dom.find('.products-item'),
            domImg = this.dom.find('.products-item').find('img');

        var inWidth = dom.width(),
            inImgWidth = domImg.attr('width'),
            inImgHeight = domImg.attr('height'),
            inHeight = Math.round(inWidth/(inImgWidth/inImgHeight));

        if (!inHeight)
            return false;

        domImg.css('height', inHeight);

        this.dom.find('.products-slider_items').css('height', inHeight+87);

    };

};