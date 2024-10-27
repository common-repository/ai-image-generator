console.log('working......');

// var frame = wp.media.view.MediaFrame.Post;
// wp.media.view.MediaFrame.Post = frame.extend({
//   initialize: function () {
//     frame.prototype.initialize.apply(this, arguments);

//     var State = wp.media.controller.State.extend({
//       insert: function () {
//         console.log("Something...");
//         this.frame.close();
//       }
//     });

//     this.states.add([
//       new State({
//         id: "ademedia",
//         search: false,
//         title: "Ade Media"
//       })
//     ]);

//     //on render
//     this.on("content:render:ademedia", this.renderAdemediaContent, this);
//   },
//   browseRouter: function (routerView) {
//     routerView.set({
//       upload: {
//         text: wp.media.view.l10n.uploadFilesTitle,
//         priority: 20
//       },
//       ademedia: {
//         text: "Ade Media",
//         priority: 30
//       },
//       browse: {
//         text: wp.media.view.l10n.mediaLibraryTitle,
//         priority: 40
//       }
//     });
//   },
//   renderAdemediaContent: function () {
//     var AdemediaContent = wp.Backbone.View.extend({
//       tagName: "div",
//       className: "ademediacontent",
//       template: wp.template("ademedia"),
//       active: !1,
//       toolbar: null,
//       frame: null
//     });

//     var view = new AdemediaContent();

//     this.content.set(view);
//   }
// });
