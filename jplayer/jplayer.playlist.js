/*
 * Playlist Object for the jPlayer Plugin
 * http://www.jplayer.org
 *
 * Copyright (c) 2009 - 2014 Happyworm Ltd
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/MIT
 *
 * Author: Mark J Panaghiston
 * Version: 2.4.1
 * Date: 19th November 2014
 *
 * Requires:
 *  - jQuery 1.7.0+
 *  - jPlayer 2.8.2+
 */

/*global jPlayerPlaylist:true */

function initSorting() {
	try {
		$.rowSorter.destroy('#jp-playlist');
		$('#jp-playlist').rowSorter({
			handler: 'td.sorter',
			onDragStart: function (tbody, row, index) {
				//log('index: ' + index);
				//console.log('onDragStart: active row\'s index is ' + index);
			},
			onDrop: function (tbody, row, new_index, old_index) {
				//log('old_index: ' + old_index + ', new_index: ' + new_index);
				//console.log('onDrop: row moved from ' + old_index + ' to ' + new_index);
				jPlaylist.playlist.move(old_index, new_index);
				if (jPlaylist.shuffled == false) {
					jPlaylist.original.move(old_index, new_index);
				}
				if (jPlaylist.current == old_index) {
					jPlaylist.current = new_index;
				}
			}
		});

		return "Done";
	} catch (err) {
		console.log(err);
	}
}

function shuffle(array) {
	var currentIndex = array.length, temporaryValue, randomIndex;

	// While there remain elements to shuffle...
	while (0 !== currentIndex) {

		// Pick a remaining element...
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;

		// And swap it with the current element.
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}

	return array;
}

(function($, undefined) {

	jPlayerPlaylist = function(cssSelector, playlist, options) {
		var self = this;

		this.current = 0;
		this.loop = false; // Flag used with the jPlayer repeat event
		this.shuffled = false;
		this.removing = false; // Flag is true during remove animation, disabling the remove() method until complete.

		this.cssSelector = $.extend({}, this._cssSelector, cssSelector); // Object: Containing the css selectors for jPlayer and its cssSelectorAncestor
		this.options = $.extend(true, {
			keyBindings: {
				next: {
					key: 221, // ]
					fn: function() {
						self.next();
					}
				},
				previous: {
					key: 219, // [
					fn: function() {
						self.previous();
					}
				},
				shuffle: {
					key: 83, // s
					fn: function() {
						self.shuffle();
					}
				}
			},
			stateClass: {
				shuffled: "jp-state-shuffled"
			}
		}, this._options, options); // Object: The jPlayer constructor options for this playlist and the playlist options

		this.playlist = []; // Array of Objects: The current playlist displayed (Un-shuffled or Shuffled)
		this.original = []; // Array of Objects: The original playlist

		this._initPlaylist(playlist); // Copies playlist to this.original. Then mirrors this.original to this.playlist. Creating two arrays, where the element pointers match. (Enables pointer comparison.)

		// Setup the css selectors for the extra interface items used by the playlist.
		this.cssSelector.details = this.cssSelector.cssSelectorAncestor + " .jp-details"; // Note that jPlayer controls the text in the title element.
		this.cssSelector.playlist = " .jp-playlist";
		this.cssSelector.next = this.cssSelector.cssSelectorAncestor + " .jp-next";
		this.cssSelector.previous = this.cssSelector.cssSelectorAncestor + " .jp-previous";
		this.cssSelector.shuffle = this.cssSelector.cssSelectorAncestor + " .jp-shuffle";
		this.cssSelector.shuffleOff = this.cssSelector.cssSelectorAncestor + " .jp-shuffle-off";

		// Override the cssSelectorAncestor given in options
		this.options.cssSelectorAncestor = this.cssSelector.cssSelectorAncestor;

		// Override the default repeat event handler
		this.options.repeat = function(event) {
			self.loop = event.jPlayer.options.loop;
		};

		// Create a ready event handler to initialize the playlist
		$(this.cssSelector.jPlayer).bind($.jPlayer.event.ready, function() {
			self._init();
		});

		// Create an ended event handler to move to the next item
		$(this.cssSelector.jPlayer).bind($.jPlayer.event.ended, function() {
			self.next();
		});

		// Create a play event handler to pause other instances
		$(this.cssSelector.jPlayer).bind($.jPlayer.event.play, function() {
			$(this).jPlayer("pauseOthers");
		});

		// Create a resize event handler to show the title in full screen mode.
		$(this.cssSelector.jPlayer).bind($.jPlayer.event.resize, function(event) {
			if(event.jPlayer.options.fullScreen) {
				$(self.cssSelector.details).show();
			} else {
				$(self.cssSelector.details).hide();
			}
		});

		// Create click handlers for the extra buttons that do playlist functions.
		$(this.cssSelector.previous).click(function(e) {
			e.preventDefault();
			self.previous();
			self.blur(this);
		});

		$(this.cssSelector.next).click(function(e) {
			e.preventDefault();
			self.next();
			self.blur(this);
		});

		$(this.cssSelector.shuffle).click(function(e) {
			e.preventDefault();
			if(self.shuffled && $(self.cssSelector.jPlayer).jPlayer("option", "useStateClassSkin")) {
				self.shuffle(false);
			} else {
				self.shuffle(true);
			}
			self.blur(this);
		});
		$(this.cssSelector.shuffleOff).click(function(e) {
			e.preventDefault();
			self.shuffle(false);
			self.blur(this);
		}).hide();

		// Put the title in its initial display state
		if(!this.options.fullScreen) {
			$(this.cssSelector.details).hide();
		}

		// Remove the empty <li> from the page HTML. Allows page to be valid HTML, while not interfereing with display animations
		$(this.cssSelector.playlist).empty();

		// Create .on() handlers for the playlist items along with the free media and remove controls.
		this._createItemHandlers();

		// Instance jPlayer
		$(this.cssSelector.jPlayer).jPlayer(this.options);
	};

	jPlayerPlaylist.prototype = {
		_cssSelector: { // static object, instanced in constructor
			jPlayer: "#jquery_jplayer_1",
			cssSelectorAncestor: "#jp_container_1"
		},
		_options: { // static object, instanced in constructor
			playlistOptions: {
				autoPlay: false,
				loopOnPrevious: false,
				shuffleOnLoop: true,
				enableRemoveControls: false,
				displayTime: 'slow',
				addTime: 'fast',
				removeTime: 'fast',
				shuffleTime: 'slow',
				itemClass: "jp-playlist-item",
				freeGroupClass: "jp-free-media",
				freeItemClass: "jp-playlist-item-free",
				removeItemClass: "jp-playlist-item-remove"
			}
		},
		option: function(option, value) { // For changing playlist options only
			if(value === undefined) {
				return this.options.playlistOptions[option];
			}

			this.options.playlistOptions[option] = value;

			switch(option) {
				case "enableRemoveControls":
					this._updateControls();
					break;
				case "itemClass":
				case "freeGroupClass":
				case "freeItemClass":
				case "removeItemClass":
					this._refresh(true); // Instant
					this._createItemHandlers();
					break;
			}
			return this;
		},
		_init: function() {
			var self = this;
			this._refresh(function() {
				if(self.options.playlistOptions.autoPlay) {
					self.play(self.current);
				} else {
					self.select(self.current);
				}
			});
		},
		_initPlaylist: function(playlist) {
			this.current = 0;
			this.shuffled = false;
			this.removing = false;
			this.original = $.extend(true, [], playlist); // Copy the Array of Objects
			this._originalPlaylist();
			initSorting();
		},
		_originalPlaylist: function() {
			var self = this;
			this.playlist = [];
			// Make both arrays point to the same object elements. Gives us 2 different arrays, each pointing to the same actual object. ie., Not copies of the object.
			$.each(this.original, function(i) {
				self.playlist[i] = self.original[i];
			});
		},
		_refresh: function(instant) {
			/* instant: Can be undefined, true or a function.
			 *	undefined -> use animation timings
			 *	true -> no animation
			 *	function -> use animation timings and excute function at half way point.
			 */
			var self = this;

			if(instant && !$.isFunction(instant)) {
				$(this.cssSelector.playlist).empty();
				$.each(this.playlist, function(i) {
					$(self.cssSelector.playlist).append(self._createListItem(self.playlist[i]));
				});
				this._updateControls();
			} else {
				var displayTime = $(this.cssSelector.playlist).children().length ? this.options.playlistOptions.displayTime : 0;

				$(this.cssSelector.playlist).slideUp(displayTime, function() {
					var $this = $(this);
					$(this).empty();
					
					$.each(self.playlist, function(i) {
						$this.append(self._createListItem(self.playlist[i]));
					});
					self._updateControls();
					if($.isFunction(instant)) {
						instant();
					}
					if(self.playlist.length) {
						$(this).slideDown(self.options.playlistOptions.displayTime);
					} else {
						$(this).show();
					}
				});
			}
		},
		_createListItem: function(media) {
			var self = this;

			// Wrap the <li> contents in a <div>
			var listItem = "<tr>";

			listItem += "<td width='25px' class='sorter'> </td>";

			// Create links to free media
			if(media.free) {
				var first = true;
				listItem += "<span class='" + this.options.playlistOptions.freeGroupClass + "'>(";
				$.each(media, function(property,value) {
					if($.jPlayer.prototype.format[property]) { // Check property is a media format.
						if(first) {
							first = false;
						} else {
							listItem += " | ";
						}
						listItem += "<a class='" + self.options.playlistOptions.freeItemClass + "' href='" + value + "' tabindex='-1'>" + property + "</a>";
					}
				});
				listItem += ")</span>";
			}

			// The title is given next in the HTML otherwise the float:right on the free media corrupts in IE6/7
			listItem += "<td><a href='javascript:;' class='" + this.options.playlistOptions.itemClass + "' tabindex='0'>" + media.title + (media.artist ? " <span class='jp-artist'>by " + media.artist + "</span>" : "") + "</a></td>";

			// Create remove control
			listItem += "<td width='75px' class='table-icon' align='right'>" +
				//"<a href='javascript:;' class='up'><img src='img/icons/arrow_up.png'></a>" +
				//"<a href='javascript:;' class='down'><img src='img/icons/arrow_down.png'></a>" +
				"<a href='javascript:;' class='queue btn btn-xs btn-default' title='Play next'><span class='glyphicon glyphicon-chevron-right'></span></a> " +
				"<a href='javascript:;' class='" + this.options.playlistOptions.removeItemClass + " btn btn-xs btn-default' title='Remove from playlist'><span class='glyphicon glyphicon-remove'></span></a>" +
				"</td>";
			listItem += "</tr>";

			return listItem;
		},
		_createItemHandlers: function() {
			var self = this;
			// Create live handlers for the playlist items
			$(this.cssSelector.playlist).off("click", "a." + this.options.playlistOptions.itemClass).on("click", "a." + this.options.playlistOptions.itemClass, function(e) {
				e.preventDefault();
				var index = $(this).parent().parent().index();
				if(self.current !== index) {
					self.play(index);
				} else {
					$(self.cssSelector.jPlayer).jPlayer("play");
				}
				self.blur(this);
			});

			// Create live handlers that disable free media links to force access via right click
			$(this.cssSelector.playlist).off("click", "a." + this.options.playlistOptions.freeItemClass).on("click", "a." + this.options.playlistOptions.freeItemClass, function(e) {
				e.preventDefault();
				$(this).parent().parent().find("." + self.options.playlistOptions.itemClass).click();
				self.blur(this);
			});

			// Create live handlers for the remove controls
			$(this.cssSelector.playlist).off("click", "a." + this.options.playlistOptions.removeItemClass).on("click", "a." + this.options.playlistOptions.removeItemClass, function(e) {
				e.preventDefault();
				var index = $(this).parent().parent().index();
				//alert(index);
				self.remove(index);
				//$("#jp-playlist tr").eq(index).remove();
				self.blur(this);
			});

			Array.prototype.move = function (old_index, new_index) {
				if (new_index >= this.length) {
					var k = new_index - this.length;
					while ((k--) + 1) {
						this.push(undefined);
					}
				}
				this.splice(new_index, 0, this.splice(old_index, 1)[0]);
				return this; // for testing purposes
			};

			$(this.cssSelector.playlist).off("click", "a.queue").on("click", "a.queue", function(e) {
				e.preventDefault();
				var index = $(this).parent().parent().index();
				if(index != self.current) {
					if(index < self.current){
						self.current -= 1;
					}
					self.playlist.move(index, self.current + 1);
					self._refresh(true);
					self._highlight(self.current);
					//$("#jp-playlist tr").eq(index).remove();
					//self.blur(this);
				} else {

				}
			});
		},
		_updateControls: function() {
			if(this.options.playlistOptions.enableRemoveControls) {
				$(this.cssSelector.playlist + " ." + this.options.playlistOptions.removeItemClass).show();
			} else {
				$(this.cssSelector.playlist + " ." + this.options.playlistOptions.removeItemClass).hide();
			}

			if(this.shuffled) {
				$(this.cssSelector.jPlayer).jPlayer("addStateClass", "shuffled");
			} else {
				$(this.cssSelector.jPlayer).jPlayer("removeStateClass", "shuffled");
			}
			if($(this.cssSelector.shuffle).length && $(this.cssSelector.shuffleOff).length) {
				if(this.shuffled) {
					$(this.cssSelector.shuffleOff).show();
					$(this.cssSelector.shuffle).hide();
				} else {
					$(this.cssSelector.shuffleOff).hide();
					$(this.cssSelector.shuffle).show();
				}
			}
		},
		_highlight: function(index) {
			if(this.playlist.length && index !== undefined) {
				$(this.cssSelector.playlist + " .jp-playlist-current").removeClass("jp-playlist-current");
				$(this.cssSelector.playlist + " tr:nth-child(" + (index + 1) + ")").addClass("jp-playlist-current").find(".jp-playlist-item").addClass("jp-playlist-current");
				// $(this.cssSelector.details + " li").html("<span class='jp-title'>" + this.playlist[index].title + "</span>" + (this.playlist[index].artist ? " <span class='jp-artist'>by " + this.playlist[index].artist + "</span>" : ""));
			}
		},
		setPlaylist: function(playlist) {
			this._initPlaylist(playlist);
			this._init();
		},
		add: function(media, playNow) {
			$(this.cssSelector.playlist).append(this._createListItem(media)).find("li:last-child").hide().slideDown(this.options.playlistOptions.addTime);
			this._updateControls();
			this.original.push(media);
			this.playlist.push(media); // Both array elements share the same object pointer. Comforms with _initPlaylist(p) system.

			if(playNow) {
				this.play(this.playlist.length - 1);
			} else {
				if(this.original.length === 1) {
					this.select(0);
				}
			}
		},
		remove: function(index) {
			var self = this;

			if(index === undefined) {
				this._initPlaylist([]);
				this._refresh(function() {
					$(self.cssSelector.jPlayer).jPlayer("clearMedia");
				});
				return true;
			} else {

				if(this.removing) {
					return false;
				} else {
					index = (index < 0) ? self.original.length + index : index; // Negative index relates to end of array.
					if(0 <= index && index < this.playlist.length) {
						this.removing = true;

						$(this.cssSelector.playlist + " tr:nth-child(" + (index + 1) + ")").slideUp(this.options.playlistOptions.removeTime, function() {
							$(this).remove();

							if(self.shuffled) {
								var item = self.playlist[index];
								$.each(self.original, function(i) {
									if(self.original[i] === item) {
										self.original.splice(i, 1);
										return false; // Exit $.each
									}
								});
								self.playlist.splice(index, 1);
							} else {
								self.original.splice(index, 1);
								self.playlist.splice(index, 1);
							}

							if(self.original.length) {
								if(index === self.current) {
									self.current = (index < self.original.length) ? self.current : self.original.length - 1; // To cope when last element being selected when it was removed
									self.select(self.current);
								} else if(index < self.current) {
									self.current--;
								}
							} else {
								$(self.cssSelector.jPlayer).jPlayer("clearMedia");
								self.current = 0;
								self.shuffled = false;
								self._updateControls();
							}

							self.removing = false;
						});
					}
					return true;
				}
			}
		},
		select: function(index) {
			index = (index < 0) ? this.original.length + index : index; // Negative index relates to end of array.
			if(0 <= index && index < this.playlist.length) {
				this.current = index;
				this._highlight(index);
				$(this.cssSelector.jPlayer).jPlayer("setMedia", this.playlist[this.current]);
			} else {
				this.current = 0;
			}
		},
		play: function(index) {
			index = (index < 0) ? this.original.length + index : index; // Negative index relates to end of array.
			if(0 <= index && index < this.playlist.length) {
				if(this.playlist.length) {
					this.select(index);
					$(this.cssSelector.jPlayer).jPlayer("play");
				}
			} else if(index === undefined) {
				$(this.cssSelector.jPlayer).jPlayer("play");
			}
		},
		pause: function() {
			$(this.cssSelector.jPlayer).jPlayer("pause");
		},
		next: function() {
			var index = (this.current + 1 < this.playlist.length) ? this.current + 1 : 0;

			if(this.loop) {
				// See if we need to shuffle before looping to start, and only shuffle if more than 1 item.
				if(index === 0 && this.shuffled && this.options.playlistOptions.shuffleOnLoop && this.playlist.length > 1) {
					this.shuffle(true, true); // playNow
				} else {
					this.play(index);
				}
			} else {
				// The index will be zero if it just looped round
				if(index > 0) {
					this.play(index);
				}
			}
		},
		previous: function() {
			var index = (this.current - 1 >= 0) ? this.current - 1 : this.playlist.length - 1;

			if(this.loop && this.options.playlistOptions.loopOnPrevious || index < this.playlist.length - 1) {
				this.play(index);
			}
		},
		shuffle: function(shuffled, playNow) {
			var self = this;

			if(shuffled === undefined) {
				shuffled = !this.shuffled;
			}

			if(shuffled || shuffled !== this.shuffled) {

				$(this.cssSelector.playlist).slideUp(this.options.playlistOptions.shuffleTime, function() {
					self.shuffled = shuffled;
					if(shuffled) {
						var nowSelected = self.playlist[self.current];
						self.playlist.sort(function() {
							return 0.5 - Math.random();
						});
						self.playlist.move(self.playlist.indexOf(nowSelected), 0);
						self._refresh(true); // Instant
						self._highlight(0);
						self.current = 0;
					} else {
						var nowSelected = self.playlist[self.current];
						self._originalPlaylist();
						self._refresh(true); // Instant
						self._highlight(self.playlist.indexOf(nowSelected));
						self.current = self.playlist.indexOf(nowSelected);
					}


					if(playNow || !$(self.cssSelector.jPlayer).data("jPlayer").status.paused) {
						//self.play(0);

					} else {
						self.select(0);
					}


					$(this).slideDown(self.options.playlistOptions.shuffleTime);
				});
			}
		},
		blur: function(that) {
			if($(this.cssSelector.jPlayer).jPlayer("option", "autoBlur")) {
				$(that).blur();
			}
		}
	};
})(jQuery);
