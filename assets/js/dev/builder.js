( function ( $ ) {

// We don't want to use the default <% %> tags because of PHP servers using ASP-like tags.
// Instead, we will use <# #> in our templates.
_.templateSettings = {
  evaluate    : /<#([\s\S]+?)#>/g,
  interpolate : /<#=([\s\S]+?)#>/g
};

// Setup our app object.
var app = {
  listView: 'verbose',
  toggleFields: 'open',
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

// Our data model for builder bar items
var nfBuilderBarItem = Backbone.Model.extend( {

} );

// Our data collection for builder bar items
var nfBuilderBarItems = Backbone.Collection.extend( {
  url: nf_rest_url + '&collection=builder_bar',
  model: nfBuilderBarItem
} );

app.Collections.fieldTypes = new nfFieldTypes();
app.Collections.fieldTypes.fetch();

$( document ).ready( function() {

  var nfBuilderBarView = Backbone.View.extend( {
    el: $( '.nf-form-builder-bar' ),

    initialize: function() {
      _.bindAll( this, 'render' );
      this.collection.fetch( { success: this.render } );
    },

    render: function () {
      var template = _.template( $( '#tmpl-nf-form-builder-bar' ).html() );
      $( this.el ).html( template );
      var that = this;
      this.collection.each( function( item ) {
        var template = _.template( $( item.get( 'template' ) ).html(), { app: app } );
        $( that.el ).append( template );
      } );
    },

    events: {
      'click .nf-item': 'click'
    },

    click: function( e ) {
      if ( 'undefined' !== typeof this[ $( e.target ).data( 'function' ) ] ) {
        this[ $( e.target ).data( 'function' ) ]( e.target );
      }
    },

    add: function( el ) {
      app.Collections.fields.add( {
        'id': 'new',
        'type': 'text',
        'label': 'New Field'
      } );
    },

    toggleView: function( el ) {

      if ( 'short' == app.listView ) {
        app.listView = 'verbose';
      } else {
        app.listView = 'short';
      }

      this.render();
      // app.Views.builder.render();
      _.each( app.Views.field, function( field, index ) {
        field.headerView.render();
      } );

    },

    toggleFields: function( el ) {

      var that = this;

      _.each( app.Views.field, function( field, index ) {
        field[ app.toggleFields ]();
      } );

      if ( 'close' == app.toggleFields ) {
        app.toggleFields = 'open';
      } else {
        app.toggleFields = 'close';
      }

      this.render();
    }

  } );

  /**
   * Main form builder view that runs on page load.
   * Loops through our fields and generates a view for each.
   * 
   * @since  3.0
   */
  var nfBuilderView = Backbone.View.extend( {
    el: $( '.nf-form-builder' ),

    initialize: function() {
      var that = this;
      _.bindAll( this, 'render' );
    
      this.collection.fetch( { success: this.render, silent: true } );
      this.collection.bind( 'add', this.render );
      this.collection.bind( 'remove', this.render );  
    },

    render: function() {
      // Empty our builder element.
      $( this.el ).empty();
      // Lets us reference 'this' in our loop.
      var that = this;
      app.Views.field = {};
      this.collection.each( function( field ) {
        var id = field.get( 'id' );
        var view = new nfFieldView( { el: that.el, model: field } );
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
      if ( 'undefined' == typeof this.active ) {
        this.active = false;
      }
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
      
      // Set our default active tab
      this.currentSection = 'basic';

      // Append our body div.
      // We append our div here so that events within the view only happen inside this element.
      template = _.template( $('#tmpl-nf-field-body' ).html() );      
      $( this.fieldDiv ).append( template );
      this.bodyDiv = $( this.fieldDiv ).find( '.nf-field-body');

      // Render our body.
      this.bodyView = new nfFieldBodyView( { fieldView: this, el: $( this.bodyDiv ), model: this.model } );
      
      return this;
    },

    toggle: function() {
      // Check to see if our body's HTML is empty.
      if ( '' == $( this.bodyDiv ).html() ) {
        this.open();
      } else {
        // It isn't empty, so deactivate this field and remove the body HTML.
        this.close();
      }
    },

    open: function() {
      this.active = true;
      // Re-render our body.
      this.bodyView.render( this.currentSection );
      // Update our fieldView with the current status
      this.headerView.render();
    },

    close: function() {
      $( this.fieldDiv ).removeClass( 'active' );
      $( this.bodyDiv ).empty();
      this.active = false;
      this.headerView.render();
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

      // Render our toggle button into our header.
      var template = _.template( $( '#tmpl-nf-field-header-toggle' ).html(), { active: this.fieldView.active } );
      $( this.el ).html( template );
      
      // Check to see if the field is active.
      if ( this.fieldView.active ) {
        // This field is active, so show the short header, which is less verbose.
        var template = $( '#tmpl-nf-field-header-content-short' ).html();
      } else {
        if ( 'verbose' == app.listView ) {
          // This field is inactive, load the longer header.
          var template = $( '#tmpl-nf-field-header-content-verbose' ).html();
        } else {
          // This field is active, so show the short header, which is less verbose.
          var template = $( '#tmpl-nf-field-header-content-short' ).html();
        }
      }

      // Render our header.
      template = _.template( template, { fieldTypes: app.Collections.fieldTypes, field: this.model } );
      $( this.el ).append( template );
      // Prevent selection so that double-clicking on the header doesn't select text.
      $( '.nf-field-header' ).disableSelection();
      $( '.nf-disable-selection' ).disableSelection();
    },

    // Listen for double clicks and clicks inside our header and on our toggle button.
    events: {
      'dblclick': 'toggleFieldView',
      'click .toggle': 'toggleFieldView'
    },

    // Calls the toggle() function of the parent view's body.
    toggleFieldView: function() {
      this.fieldView.toggle();
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
      console.log( this.fieldView.active );
      if ( this.fieldView.active ) {
        this.render( this.fieldView.currentSection );
      }
    },

    render: function( section ) {
      // Set our active class for the field.
      $( this.fieldView.fieldDiv ).addClass( 'active' );
      // Empty our body div just incase we're changing types or toggling.
      $( this.fieldView.bodyDiv ).empty();

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
      
      return this;
    }

  } );

  /**
   * View that represents our sidebar tabs.
   * 
   * @since  3.0
   */
  var nfFieldSidebarView = Backbone.View.extend( {

    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      // Add our passed fieldView to 'this' context.
      // This lets us access parent view stuff from within the child.
      this.fieldView = vars.fieldView;
      this.render( vars.section );
    },

    render: function( section ) {
      // Get our current field type.
      var fieldType = this.model.get( 'type' );
      // Get our field type model.
      fieldType = app.Collections.fieldTypes.get( fieldType );
      // Get our sidebars from the field type.
      sidebars = fieldType.get( 'data' ).sidebars;
      // Generate our html and append it to the sidebarDiv element.
      var template = _.template( $( '#tmpl-nf-field-tabs' ).html(), { fieldTypes: app.Collections.fieldTypes.models, field: this.model, sidebars: sidebars, section: section } );
      $( this.el ).append( template );
    },

    // Listen for clicks of tabs.
    events: {
      'click a': 'changeSettings',
      'change select' : 'changeType'
    },

    changeSettings: function( e ) {
      e.preventDefault();
      // Remove the active class from all tabs inside this view.
      $( this.el ).find( '.nf-field-tab' ).removeClass( 'active' );
      // Add an active class to the tab we clicked on.
      $( e.target ).parent().addClass( 'active' );
      // Get our desired section.
      var section = $( e.target ).data( 'section' );
      // Redraw our contentView with the new section.
      this.fieldView.bodyView.contentView.render( section );
    },

    changeType: function( e ) {
      // Update our model with the new field type.
      this.model.set( 'type', $( e.target ).val() );
      // Re-render our header based upon the new field type.
      this.fieldView.headerView.render();
      // Re-render our body view based upon the new field type.
      this.fieldView.bodyView.render( this.fieldView.currentSection );
    }

  } );

  /**
   * View that represents our settings content
   * 
   * @since  3.0
   */
  var nfFieldContentView = Backbone.View.extend( {

    initialize: function( vars ) {
      _.bindAll( this, 'render' );
      // Add our passed fieldView to 'this' context.
      // This lets us access parent view stuff from within the child.
      this.fieldView = vars.fieldView;
      this.render( this.fieldView.currentSection );
    },

    render: function( section ) {
      // Set our current section
      this.fieldView.currentSection = section;

      // Get our field type.
      var fieldType = this.model.get( 'type' );
      // Get our settings based upon field type.
      var settings = app.Collections.fieldTypes.get( fieldType );
      settings = settings.get( 'data' ).settings[section];

      // Reset our content to an empty string before appending it.
      $( this.el ).html('');
      var template = '';
      var that = this;
      // Loop through each of our settings and output the template for that setting.
      _.each( settings, function( setting, id ) {
        // Our templates use setting.id, but that's not a part of the setting.
        // Set setting.id based upon the id of this setting.
        setting.id = id;
        template = _.template( $( '#tmpl-nf-field-' + setting.type ).html(), { setting: setting } );
        $( that.el ).append( template );
      } );
      return this;
    },

    // Watch for changes to our inputs so that we can update our field model.
    events: {
      'change input': 'changeInput'
    },

    // Update our field model with the new value.
    changeInput: function() {
      console.log( 'input changed' );
    }

  } );

  app.Collections.builderBarItems = new nfBuilderBarItems();
  app.Views.builderBar = new nfBuilderBarView( { collection: app.Collections.builderBarItems } );
  app.Collections.fields = new nfFields();
  app.Views.builder = new nfBuilderView( { collection: app.Collections.fields } );

} );

})(jQuery)