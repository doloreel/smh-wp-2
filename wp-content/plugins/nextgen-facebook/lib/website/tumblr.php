<?php
/*
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Copyright 2012-2014 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuSharingTumblr' ) && class_exists( 'NgfbSubmenuSharing' ) ) {

	class NgfbSubmenuSharingTumblr extends NgfbSubmenuSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
		}

		protected function get_rows( $metabox, $key ) {
			$buttons_html = '<div class="btn_wizard_row clearfix" id="button_styles">';
			$buttons_style = empty( $this->p->options['tumblr_button_style'] ) ? 
				'share_1' : $this->p->options['tumblr_button_style'];
			foreach ( range( 1, 4 ) as $i ) {
				$buttons_html .= '<div class="btn_wizard_column share_'.$i.'">';
				foreach ( array( '', 'T' ) as $t ) {
					$buttons_html .= '
						<div class="btn_wizard_example clearfix">
						<label for="share_'.$i.$t.'">
						<input type="radio" id="share_'.$i.$t.'" 
							name="'.$this->form->options_name.'[tumblr_button_style]" 
							value="share_'.$i.$t.'" '.
							checked( 'share_'.$i.$t, $buttons_style, false ).'/>
						<img src="'.$this->p->util->get_cache_url( 'http://platform.tumblr.com/v1/share_'.$i.$t.'.png' ).'" 
							height="20" class="share_button_image"/>
						</label>
						</div>
					';
				}
				$buttons_html .= '</div>';
			}
			$buttons_html .= '</div>';

			return array(
				$this->p->util->th( 'Show Button in', 'short highlight', null,
				'The Tumblr button shares a <em>custom image ID</em>, a <em>featured</em> image, or an <em>attached</em> 
				image that is equal to or larger than the \'Image Dimensions\' you have chosen 
				(when the <em>Use Featured Image</em> option is checked), embedded video, the content of <em>quote</em> custom Posts, 
				or (lastly) the webpage link.' ).'<td>'.
				( $this->show_on_checkboxes( 'tumblr' ) ).'</td>',

				$this->p->util->th( 'Preferred Order', 'short' ).'<td>'.
				$this->form->get_select( 'tumblr_order', 
					range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 
						'short' ).'</td>',

				$this->p->util->th( 'JavaScript in', 'short' ).'<td>'.
				$this->form->get_select( 'tumblr_js_loc', $this->js_locations ).'</td>',

				$this->p->util->th( 'Button Style', 'short' ).'<td>'.$buttons_html.'</td>',

				$this->p->util->th( 'Use Featured Image', 'short' ).'<td>'.
				$this->form->get_checkbox( 'tumblr_photo' ).'</td>',

				$this->p->util->th( 'Image Dimensions', 'short' ).
				'<td>Width '.$this->form->get_input( 'tumblr_img_width', 'short' ).' x '.
				'Height '.$this->form->get_input( 'tumblr_img_height', 'short' ).' &nbsp; '.
				'Crop '.$this->form->get_checkbox( 'tumblr_img_crop' ).'</td>',

				$this->p->util->th( 'Media Caption', 'short' ).'<td>'.
				$this->form->get_select( 'tumblr_caption', $this->captions ).'</td>',

				$this->p->util->th( 'Caption Length', 'short' ).'<td>'.
				$this->form->get_input( 'tumblr_cap_len', 'short' ).' Characters or less</td>',

				$this->p->util->th( 'Link Description', 'short' ).'<td>'.
				$this->form->get_input( 'tumblr_desc_len', 'short' ).' Characters or less</td>',
			);
		}
	}
}

if ( ! class_exists( 'NgfbSharingTumblr' ) && class_exists( 'NgfbSharing' ) ) {

	class NgfbSharingTumblr {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'tumblr_on_content' => 0,
					'tumblr_on_excerpt' => 0,
					'tumblr_on_admin_edit' => 1,
					'tumblr_on_sidebar' => 0,
					'tumblr_order' => 9,
					'tumblr_js_loc' => 'footer',
					'tumblr_button_style' => 'share_1',
					'tumblr_desc_len' => 300,
					'tumblr_photo' => 1,
					'tumblr_img_width' => 800,
					'tumblr_img_height' => 800,
					'tumblr_img_crop' => 0,
					'tumblr_caption' => 'both',
					'tumblr_cap_len' => 500,
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
			$this->p->util->add_img_sizes_from_opts( array( 'tumblr_img' => 'tumblr' ) );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( &$atts = array(), &$opts = array() ) {
			if ( empty( $opts ) ) 
				$opts =& $this->p->options;
			global $post; 
			$use_post = array_key_exists( 'use_post', $atts ) ? $atts['use_post'] : true;
			$source_id = $this->p->util->get_source_id( 'tumblr', $atts );
			$atts['add_page'] = array_key_exists( 'add_page', $atts ) ? $atts['add_page'] : true;	// get_sharing_url argument
			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $use_post, $atts['add_page'], $source_id ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], 
					$use_post, $atts['add_page'], $source_id );

			if ( empty( $atts['size'] ) ) 
				$atts['size'] = $this->p->cf['lca'].'-tumblr';

			// only use featured image if 'tumblr_photo' option allows it
			if ( empty( $atts['photo'] ) && $opts['tumblr_photo'] ) {
				if ( empty( $atts['pid'] ) ) {
					// allow on index pages only if in content (not a widget)
					if ( ! empty( $post ) && $use_post == true ) {
						$pid = $this->p->addons['util']['postmeta']->get_options( $post->ID, 'og_img_id' );
						$pre = $this->p->addons['util']['postmeta']->get_options( $post->ID, 'og_img_id_pre' );
						if ( ! empty( $pid ) )
							$atts['pid'] = $pre == 'ngg' ? 'ngg-'.$pid : $pid;
						elseif ( $this->p->is_avail['postthumb'] == true && has_post_thumbnail( $post->ID ) )
							$atts['pid'] = get_post_thumbnail_id( $post->ID );
						else $atts['pid'] = $this->p->media->get_first_attached_image_id( $post->ID );
					}
				}
				if ( ! empty( $atts['pid'] ) )
					list( $atts['photo'], $atts['width'], $atts['height'],
						$atts['cropped'] ) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );
			}

			// check for custom or embedded videos
			if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) ) {
				// allow on index pages only if in content (not a widget)
				if ( ! empty( $post ) && $use_post == true ) {
					$atts['embed'] = $this->p->addons['util']['postmeta']->get_options( $post->ID, 'og_vid_url' );
					if ( empty( $atts['embed'] ) ) {
						$videos = array();
						$videos = $this->p->media->get_content_videos( 1, $post->ID, false );
						if ( ! empty( $videos[0]['og:video'] ) ) 
							$atts['embed'] = $videos[0]['og:video'];
					}
				}
			}

			// if no image or video, then check for a 'quote'
			if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) && empty( $atts['quote'] ) ) {
				// allow on index pages only if in content (not a widget)
				if ( ! empty( $post ) && $use_post == true ) {
					if ( get_post_format( $post->ID ) == 'quote' ) 
						$atts['quote'] = $this->p->webpage->get_quote();
				}
			}

			// we only need the caption, title, or description for some types of shares
			if ( ! empty( $atts['photo'] ) || ! empty( $atts['embed'] ) ) {
				// check for custom image or video caption
				if ( empty( $atts['caption'] ) && ! empty( $post ) && $use_post == true ) 
					$atts['caption'] = $this->p->addons['util']['postmeta']->get_options( $post->ID, 
						( ! empty( $atts['photo'] ) ? 'tumblr_img_desc' : 'tumblr_vid_desc' ) );
				if ( empty( $atts['caption'] ) ) 
					$atts['caption'] = $this->p->webpage->get_caption( $opts['tumblr_caption'], 
						$opts['tumblr_cap_len'], $use_post );
			} else {
				if ( empty( $atts['title'] ) ) 
					$atts['title'] = $this->p->webpage->get_title( null, null, $use_post);
				if ( empty( $atts['description'] ) ) 
					$atts['description'] = $this->p->webpage->get_description( $opts['tumblr_desc_len'], '...', $use_post );
			}

			// define the button, based on what we have
			$query = '';
			if ( ! empty( $atts['photo'] ) ) {
				$query .= 'photo?source='. urlencode( $atts['photo'] );
				$query .= '&amp;clickthru='.urlencode( $atts['url'] );
				$query .= '&amp;caption='.urlencode( $atts['caption'] );
			} elseif ( ! empty( $atts['embed'] ) ) {
				$query .= 'video?embed='.urlencode( $atts['embed'] );
				$query .= '&amp;caption='.urlencode( $atts['caption'] );
			} elseif ( ! empty( $atts['quote'] ) ) {
				$query .= 'quote?quote='.urlencode( $atts['quote'] );
				$query .= '&amp;source='.urlencode( $atts['title'] );
			} elseif ( ! empty( $atts['url'] ) ) {
				$query .= 'link?url='.urlencode( $atts['url'] );
				$query .= '&amp;name='.urlencode( $atts['title'] );
				$query .= '&amp;description='.urlencode( $atts['description'] );
			}
			if ( empty( $query ) ) return;

			$html = '<!-- Tumblr Button --><div '.$this->p->sharing->get_css( 'tumblr', $atts ).'>';
			$html .= '<a href="http://www.tumblr.com/share/'. $query.'" title="Share on Tumblr">';
			$html .= '<img border="0" alt="Share on Tumblr" src="'.
				$this->p->util->get_cache_url( 'http://platform.tumblr.com/v1/'.$opts['tumblr_button_style'].'.png' ).'" /></a></div>';
			$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}

		// the tumblr host does not have a valid SSL cert, and it's javascript does not work in async mode
		public function get_js( $pos = 'id' ) {
			$this->p->debug->mark();
			$js_url = $this->p->util->get_cache_url( 'http://platform.tumblr.com/v1/share.js' );

			return '<script type="text/javascript" id="tumblr-script-'.$pos.'" src="'.$js_url.'"></script>';
		}
	}
}

?>
