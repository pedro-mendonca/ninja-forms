( function ( $ ) {

// jQuery plugin to decode querystring params and return an object.
// i.e. foo=bar&test=blah ->  { foo: bar, test: blah }
var re = /([^&=]+)=?([^&]*)/g;
var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};
$.parseParams = function(query) {
    var params = {}, e;
    while ( e = re.exec(query) ) { 
        var k = decode( e[1] ), v = decode( e[2] );
        if (k.substring(k.length - 2) === '[]') {
            k = k.substring(0, k.length - 2);
            (params[k] || (params[k] = [])).push(v);
        }
        else params[k] = v;
    }
    return params;
};

// jQuery plugin that breaks apart a URL and returns the pieces.
// This is needed to make our hash-less routing system work properly.
$.parseURL = function( href ) {
    var match = href.match(/^(https?\:)\/\/(([^:\/?#]*)(?:\:([0-9]+))?)(\/[^?#]*)(\?[^#]*|)(#.*|)$/);
    return match && {
        protocol: match[1],
        host: match[2],
        hostname: match[3],
        port: match[4],
        pathname: match[5],
        query: match[6],
        hash: match[7]
    }
}

// We don't want to use the default <% %> tags because of PHP servers using ASP-like tags.
// Instead, we will use <# #> in our templates.
_.templateSettings = {
  evaluate    : /<#([\s\S]+?)#>/g,
  interpolate : /<#=([\s\S]+?)#>/g
};

// Setup our app object.
var app = {
  Models: {},
  Collections: {
    fields: {}
  },
  Views: {
    fields: {}
  }
};

// Our data model for a field
var nfField = Backbone.Model.extend( {

} );

// Our data collection for fields
var nfFields = Backbone.Collection.extend( {
  url: nf_rest_url,
  model: nfField
} );

$( document ).ready( function() {

  var nfFieldRouter = Backbone.Router.extend({
    routes: {
        '*notFound' : 'default',
        ''          : 'default'
    },

    default: function( pathname, query ) {
      var query = $.parseParams( query );
      if ( 'undefined' !== typeof query.section ) {
       
        var section = query.section;
      } else {
        // Just here for testing. Should be removed later.
        var section = 'basic';
      }

    },

    initializeRouter: function () {
      Backbone.history.start({ pushState: true });
      $( document ).on( 'click', 'a[data-nf-backbone]', function ( evt ) {

        var href = $( this ).attr( 'href' );
        var protocol = this.protocol + '//';

        if ( href.slice( protocol.length ) !== protocol ) {
          evt.preventDefault();
          href = $.parseURL( href );
          href = href.pathname + href.query;
          app.router.navigate( href, { trigger: true } );
        }
      } );
    }

  });

  var nfBuilderView = Backbone.View.extend( {
    el: $( '.nf-form-builder' ),

    initialize: function() {
      var that = this;
      _.bindAll( this, 'render' );
      this.collection.fetch( { success: this.render } );
    },

    render: function() {
      this.collection.each( function( field ) {
          app.Views.field = new nfFieldView( { model: field } );
      } );
    }

  } );

  var nfFieldView = Backbone.View.extend( {
    el: $( '.nf-field-sidebar' ), // attaches `this.el` to an existing element.
    sidebarTemplate: $('#tmpl-nf-field-sidebar').html(),

    initialize: function(){
      _.bindAll( this, 'render' ); // fixes loss of context for 'this' within methods
      console.log( this.model );
      // this.render( 'basic' ); // not all views are self-rendering. This one is.
    },

    render: function( section ){
      // Render our header.
      // app.Views.fields[3].header = new nfFieldHeaderView( { field_id: 3 } );
      // Render our sidebar.
      // app.Views.fields[3].sidebar = new nfFieldSidebarView( { field_id: 3, section: section })
      // Render our settings section.
      // app.Views.fields[3].settings = new nfFieldSettingsView( { field_id: 3 } );
      
      return this;
    }

  } );

  var nfFieldHeaderView = Backbone.View.extend( {
    el: $( '.nf-field' ),

    initialize: function() {
      _.bindAll( this, 'render' ),
      this.render();
    },

    render: function() {
      var template = _.template( $( '#tmpl-nf-field-header' ).html() );
      $( this.el ).html( template );
    }

  } );

  var nfFieldSidebarView = Backbone.View.extend( {
    el: $( '.nf-field-sidebar' ),

    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      this.field_id = vars.field_id;
      this.render( vars.section );
    },

    render: function( section ) {
      var template = _.template( $( '#tmpl-nf-field-sidebar' ).html(), { section: section } );
      $( this.el ).html( template );
    }

  } );

  var nfFieldSettingsView = Backbone.View.extend( {
    el: $( '.nf-field-content .inside' ),

    initialize: function( field_id ) {
      _.bindAll( this, 'render' );
      this.field_id = field_id;
      this.render();
    },

    render: function() {
      var settings = {};

      settings.label = { type: 'text', label: 'Label', id: 'label', placeholder: 'My Field', desc: 'The text that identifies the field for your user.' };
      settings.label_pos = { type: 'select', label: 'Label Position', id: 'label_pos', desc: 'This is where the label is displayed in relation to the element.' };

      $( this.el ).html('');
      var template = '';
      var that = this;
      _.each( settings, function( setting ) {
        template = _.template( $( '#tmpl-nf-field-' + setting.type ).html(), { setting: setting } );
        $( that.el ).append( template );
      } );
      return this;
    },

    events: {
      'change input': 'changeInput'
    },

    changeInput: function() {
      console.log( 'input changed' );
    }

  } );

  app.router = new nfFieldRouter();
  app.router.initializeRouter();
  app.Collections.fields = new nfFields();
  app.Views.builder = new nfBuilderView( { collection: app.Collections.fields } );

} );

})(jQuery)