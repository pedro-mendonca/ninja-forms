( function ( $ ) {

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
  url: nf_rest_url + '&collection=fields',
  model: nfField
} );

// Our data model for field type
var nfFieldType = Backbone.Model.extend( {

} );

// Our data collection for field types
var nfFieldTypes = Backbone.Collection.extend( {
  url: nf_rest_url + '&collection=field_types',
  model: nfFieldType
} );

app.Collections.fieldTypes = new nfFieldTypes();
app.Collections.fieldTypes.fetch();

$( document ).ready( function() {

  /**
   * Main view that runs on page load.
   * Loops through our fields and generates a view for each.
   * 
   * @since  3.0
   */
  var nfBuilderView = Backbone.View.extend( {
    el: $( '.nf-form-builder' ),

    initialize: function() {
      var that = this;
      _.bindAll( this, 'render' );
      this.collection.fetch( { success: this.render } );
    },

    render: function() {
      var that = this;
      this.collection.each( function( field ) {
          app.Views.field = new nfFieldView( { el: that.el, model: field } );
      } );
    }

  } );

  /**
   * View that represents a single field view in our editor.
   *
   * The HTML structure of the field is:
   * <field>
   *   <header></header>
   *   <body>
   *     <sidebar></sidebar>
   *     <content>
   *       <inside></inside>
   *     </content>  
   *   </body>
   * </field>
   *  
   * @since  3.0
   */
  var nfFieldView = Backbone.View.extend( {

    initialize: function(){
      _.bindAll( this, 'render' ); // fixes loss of context for 'this' within methods
      this.render(); // not all views are self-rendering. This one is.
    },

    render: function(){
      // Render our field div container
      var template = _.template( $( '#tmpl-nf-field' ).html() );

      // Work around for getting the newly appended html element as an object.
      // This lets us reference these html elements later.
      var fieldDiv = $( '<div>' ).html( template );
      this.fieldDiv = $( fieldDiv ).find( '.nf-field' );
      $( this.el ).append( this.fieldDiv );
      // Our header div is already a part of the nf-field div.
      // So we just have to find it.
      this.headerDiv = $( this.fieldDiv ).find( '.nf-field-header' );

      // Render our header.
      this.headerView = new nfFieldHeaderView( { fieldView: this, el: $( this.headerDiv ), model: this.model } );
      
      // Append our body div.
      // We append our div here so that events within the view only happen inside this element.
      template = _.template( $('#tmpl-nf-field-body' ).html() );      
      $( this.fieldDiv ).append( template );
      this.bodyDiv = $( this.fieldDiv ).find( '.nf-field-body');

      // Render our body.
      this.bodyView = new nfFieldBodyView( { fieldView: this, el: $( this.bodyDiv ), model: this.model } );
      
      return this;
    }

  } );

  /**
   * View that represents each field's header row.
   * 
   * @since  3.0
   */
  var nfFieldHeaderView = Backbone.View.extend( {

    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      // Add our passed fieldView to 'this' context.
      // This lets us access parent view stuff from within the child.
      this.fieldView = vars.fieldView;
      this.render();
    },

    render: function() {
      // Render our header.
      var template = _.template( $( '#tmpl-nf-field-header' ).html(), { field: this.model } );
      $( this.el ).html( template );
      // Prevent selection so that double-clicking on the header doesn't select text.
      $( '.nf-field-header' ).disableSelection();
    },

    // Listen for double clicks and clicks inside our header and on our toggle button.
    events: {
      'dblclick': 'toggleFieldView',
      'click .toggle': 'toggleFieldView'
    },

    // Calls the toggle() function of the parent view's body.
    toggleFieldView: function() {
      this.fieldView.bodyView.toggle();
    }

  } );

  /**
   * View that represents our field body.
   * 
   * The HTML structure of the body is:
   * <body>
   *   <sidebar></sidebar>
   *   <content>
   *     <inside></inside>
   *   </content>  
   * </body>
   * 
   * @since  3.0
   */
  var nfFieldBodyView = Backbone.View.extend( {
    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      // Add our passed fieldView to 'this' context.
      // This lets us access parent view stuff from within the child.
      this.fieldView = vars.fieldView;
      this.render( 'basic' );
    },

    render: function( section ) {
      // Set our active class for the field.
      $( this.fieldView.fieldDiv ).addClass( 'active' );
      
      // Append our sidebar div
      // We append our div here so that events within the view only happen inside this element.
      template = _.template( $('#tmpl-nf-field-sidebar' ).html() );      
      $( this.el ).append( template );
      this.sidebarDiv = $( this.el ).find( '.nf-field-sidebar' );

      // Render our sidebar.
      this.sidebarView = new nfFieldSidebarView( { fieldView: this.fieldView, el: this.sidebarDiv, model: this.model, section: section } );
     
      // Append our content div
      // We append our div here so that events within the view only happen inside this element.
      template = _.template( $('#tmpl-nf-field-content' ).html() );      
      $( this.el ).append( template );
      this.contentDiv = $( this.el ).find( '.nf-field-content .inside' );

      // Render our content section.
      this.contentView = new nfFieldContentView( { fieldView: this.fieldView, el: this.contentDiv, model: this.model } );
    },

    toggle: function() {
      // Check to see if our body's HTML is empty.
      if ( '' == $( this.el ).html() ) {
        //If it is empty, re-render our body.
        this.render( 'basic' );
      } else {
        // It isn't empty, so deactivate this field and remove the body HTML.
        $( this.fieldView.fieldDiv ).removeClass( 'active' );
        $( this.el ).empty();
      }
    }

  } );

  var nfFieldSidebarView = Backbone.View.extend( {

    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      this.fieldView = vars.fieldView;
      this.render( vars.section );
    },

    render: function( section ) {
      // Get our current field type.
      var fieldType = this.model.get( 'type' );
      fieldType = app.Collections.fieldTypes.get( fieldType );
      sidebars = fieldType.get( 'data' ).sidebars;

      var template = _.template( $( '#tmpl-nf-field-tabs' ).html(), { sidebars: sidebars, section: section } );
      $( this.el ).append( template );
    },

    events: {
      'click a': 'change'
    },

    change: function( e ) {
      e.preventDefault();
      $( this.el ).find( '.nf-field-tab' ).removeClass( 'active' );
      $( e.target ).parent().addClass( 'active' );
      var section = $( e.target ).data( 'section' );
      this.fieldView.bodyView.contentView.render( section );
    }

  } );

  var nfFieldContentView = Backbone.View.extend( {

    initialize: function( field_id ) {
      _.bindAll( this, 'render' );
      this.field_id = field_id;
      this.render( 'basic' );
    },

    render: function( section ) {
      var fieldType = this.model.get( 'type' );
      var settings = app.Collections.fieldTypes.get( fieldType );
      settings = settings.get( 'data' ).settings[section];

      $( this.el ).html('');
      var template = '';
      var that = this;
      _.each( settings, function( setting, id ) {
        setting.id = id;
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

  app.Collections.fields = new nfFields();
  app.Views.builder = new nfBuilderView( { collection: app.Collections.fields } );

} );

})(jQuery)