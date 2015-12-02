/**
  Initializing the Jq function for `jqSlide`
  So that we can access as $('element').jqSlide();
**/
(function($) {
  $.fn.jqSlide = function(options) {
    jqSlide.init(this, options);
  };
}(jQuery));

/**
  Master jqSlide object
**/

var jqSlide = {

  totalSlides: 0,

  /**
    @method getActivesliderIndex
    @returns 'Integer', the index position of the current active slide will be returned
  **/
  getActivesliderIndex:function() {
    return parseInt($('.active').attr('id').split(' ')[0].split('-')[1]);
  },

  /**
    @method init
    @param 'jq Element'
    Initiates the construction of DOM process
  **/
  init: function(element, options) {
    var headerElements = element.children('h2');
    var contentElements = element.children('p');
    var initialSlide = 1;

    //Throws log when the header and content count mismatch
    if (headerElements.length != contentElements.length) {
      console.log('Header and content Mismatch');
      return;
    }

    //Set the total slide value
    this.totalSlides = headerElements.length;

    //Ditch the existing DOM
    element.empty();

    //Constructing the new DOM based on the cached headerElements and contentElements
    this.constructSlider(element, headerElements, contentElements);

    //To set the slide to default or set slide 1 initially
    if (options) {
      var defaultSlide = options.defaultSlide;
      initialSlide = defaultSlide && !isNaN(parseInt(defaultSlide)) && isFinite(defaultSlide) && (parseInt(defaultSlide) <= this.totalSlides) ? defaultSlide : 1;
    }

    this.slideMe(initialSlide);
  },

  /**
    @method constructSlider
    @param `element` @type DOM node to which the slider needs to be constructed.
            `headerElements` @type Array of DOM nodes which holdes the slider header content.
            `contentElements` @type Array of DOM nodes which holdes the slider body content.
    @usage Constructs the DOM for the slider
  **/
  constructSlider: function(element, headerElements, contentElements) {

    //Generating the Slider header tabs
    var nodeString = '<div class="tab-slider" id="slider"><ul class="slide-navbar">';
    $.each(headerElements, function(i, value) {
      nodeString += '<li id="slide-indicator-' + (i + 1) + '" onclick="jqSlide.initiateSlide(' + (i + 1) + ');">' + $(headerElements[i]).html() + '</li>';
    });

    nodeString += '</ul></div>';

    //Generating the Slider header in select format for the Mobile version
    nodeString += '<div class="select-slider"><select id="select-slider" name="navigation" onchange="jqSlide.slideSelect(this);">';
    nodeString += '<option disabled="disabled" selected="selected">Menu</option>';
    $.each(headerElements, function(i, value) {
      nodeString += '<option value="slider-' + (i + 1) + '">' + $(headerElements[i]).html() + '</option>';
    });

    nodeString += '</select></div>';

    //Generating the Slider contents
    nodeString += '<div class="slider-wrapper"><div class="slider-container">';
    $.each(contentElements, function(i, value) {
      nodeString += '<div id="slider-' + (i + 1) + '"><p>' + $(contentElements[i]).html() + '</p></div>';
    });

    nodeString += '</div></div>';

    //Finally appending the generated nodeString to the DOM element
    element.append(nodeString);
  },

  /**
    @method slideSelect
    @param 'Integer', the index position of the slide to be selected
  **/
  slideSelect: function(select) {
    var selectedIndex = parseInt(select.value.split('-')[1]);
    this.initiateSlide(selectedIndex);
  },

  /**
    @method slideMe
    @param 'Integer', the index position of the slide to be selected
    It is the core function which applies the sppropriate classes and css for the DOM elements for the slide
  **/
  slideMe:function(index) {
    var totalSlides = this.totalSlides;
    var value = -((index - 1) * (100 / totalSlides));
    var distributedwidth = 100 /  totalSlides;

    //Here where the actual sliding takes pace by means of applying the transform css property.
    $('.slider-container').css('transform', 'translate3d(' + value + '%, 0px, 0px)')
    $('.slider-container').css('width', (totalSlides * 100) + '%')

    for (i = 1 ; i <= totalSlides; i++) {
      var indexPostion = (index - i) * 100;
      var slideEle = $('#slider-' + i);
      slideEle.css('width', distributedwidth + '%')
      slideEle.removeClass('active');
      $('#slide-indicator-' + i).removeClass('active-tab');

      //To activate the selected slide
      if (indexPostion == 0) {
        slideEle.addClass('active');
        $('#slide-indicator-' + i).addClass('active-tab');
        $('#select-slider').val('slider-' + index)
      }
    }
  },

  /**
    @method initiateSlide
    @returns 'Integer', the index position of the slide to be selected

  **/
  initiateSlide:function(index) {
    var currentActive = this.getActivesliderIndex();

    //If no active slides, navigate to the initial slide
    if (currentActive == '') {
      this.slideMe(1);
    }

    //If the selected tab is the active tab, don't do anything. silently return;
    if (index === currentActive) {
      return;
    } else {
      this.slideMe(index);
    }
  }
}
