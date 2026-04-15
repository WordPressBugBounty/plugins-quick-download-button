import { registerBlockType } from '@wordpress/blocks'; 
import { SVG, Path } from '@wordpress/primitives';
import { __ } from '@wordpress/i18n';
import {
    ColorPalette,
    InspectorControls,
    MediaUpload,
    BlockControls,
    useBlockProps,
    RichText,
    InnerBlocks
} from '@wordpress/block-editor';
import {
    Button,
    PanelBody,
    TextControl,
    TextareaControl,
    ToggleControl,
    RadioControl, __experimentalNumberControl as NumberControl,
	ToolbarGroup,
    ToolbarButton,
    RangeControl,
    SelectControl
} from '@wordpress/components';

import { useState, useEffect, RawHTML } from '@wordpress/element';

import colors from './colors';

const download_button_icon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
		<Path d="M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z" />
	</SVG>
);

const blockIcon = <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="256.000000pt" height="256.000000pt" viewBox="0 0 256.000000 256.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,256.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M622 1807 c-454 -454 -462 -463 -462 -502 0 -39 8 -48 534 -574 l534 -534 43 6 c41 5 65 28 556 519 283 282 521 526 529 541 33 61 32 62 -449 545 -249 248 -455 452 -459 452 -5 0 -7 -208 -6 -462 l3 -463 222 -5 223 -5 -302 -249 c-166 -137 -307 -249 -313 -249 -6 0 -149 112 -318 249 l-306 249 219 3 220 2 0 470 c0 259 -1 470 -3 470 -1 0 -210 -208 -465 -463z"/></g></svg>;

let user_roles = qdbu_data['qdbn_user_roles'];

const alignMap = { left: 'flex-start', center: 'center', right: 'flex-end' };

/* ── Built-in icon sets ───────────────────────────────────────────── */
const QDB_DOWNLOAD_ICONS = [
    { id: 'default',  label: 'Arrow',   path: 'M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z' },
    { id: 'cloud',    label: 'Cloud',   path: 'M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z' },
    { id: 'circle',   label: 'Circle',  path: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 5h2v6h3l-4 4-4-4h3V7z' },
    { id: 'file-dl',  label: 'File',    path: 'M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z' },
    { id: 'inbox',    label: 'Inbox',   path: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5v-3h3.56c.69 1.19 1.97 2 3.45 2s2.75-.81 3.45-2H19v3zm0-5h-4.99c0 1.1-.9 1.99-2 1.99S10 15.1 10 14H5V5h14v9z' },
    { id: 'save',     label: 'Save',    path: 'M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z' },
    { id: 'bolt',     label: 'Bolt',    path: 'M7 2v11h3v9l7-12h-4l4-8z' },
    { id: 'none',     label: 'None',    path: null },
    { id: 'custom',   label: 'Custom',  path: null },
];

const QDB_SIZE_ICONS = [
    { id: 'folder',   label: 'Folder',  path: 'M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z' },
    { id: 'archive',  label: 'Archive', path: 'M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z' },
    { id: 'info',     label: 'Info',    path: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z' },
    { id: 'chip',     label: 'Size',    path: 'M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z' },
    { id: 'none',     label: 'None',    path: null },
    { id: 'custom',   label: 'Custom',  path: null },
];

/**
 * Render a built-in or custom SVG icon.
 * For built-in icons: renders inline SVG from the path map.
 * For 'custom': renders user-pasted SVG via RawHTML.
 */
function qdbRenderIcon( iconSet, id, customSvg, size = 20 ) {
    if ( id === 'custom' ) {
        return customSvg ? <RawHTML>{ customSvg }</RawHTML> : null;
    }
    const icon = iconSet.find( i => i.id === id );
    if ( ! icon || ! icon.path ) return null;
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width={ size } height={ size } aria-hidden="true">
            <path fill="currentColor" d={ icon.path } />
        </svg>
    );
}

/**
 * Render the icon picker grid used in InspectorControls panels.
 */
function QdbIconPicker( { icons, selected, customSvg, onSelect, onCustomSvgChange, label } ) {
    return (
        <div>
            <label className="components-base-control__label qdbu-editor-label">{ label }</label>
            <div className="qdb-icon-picker">
                { icons.map( icon => (
                    <button
                        key={ icon.id }
                        type="button"
                        className={ `qdb-icon-btn${ selected === icon.id ? ' is-selected' : '' }${ icon.id === 'custom' ? ' qdb-icon-btn--text' : '' }` }
                        onClick={ () => onSelect( icon.id ) }
                        title={ icon.label }
                    >
                        { icon.path
                            ? <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d={ icon.path } /></svg>
                            : <span>{ icon.label }</span>
                        }
                    </button>
                ) ) }
            </div>
            { selected === 'custom' && (
                <TextareaControl
                    label={ __( 'Paste SVG code', 'quick-download-button' ) }
                    value={ customSvg }
                    onChange={ onCustomSvgChange }
                    rows={ 3 }
                    help={ __( 'Paste a valid <svg>…</svg> string.', 'quick-download-button' ) }
                />
            ) }
        </div>
    );
}

registerBlockType( 'quick-download-button/download-button', {
    title: __('Download Button','quick-download-button'),
    icon: blockIcon,
    description: __('Use download button for your file download link.', 'quick-download-button'),
    category: 'widgets',
    keywords: [
        __('download', 'quick-download-button'),
        __('button', 'quick-download-button'),
        __('file', 'quick-download-button'),
    ],
    attributes: {
        buttonStyle : {
            type: 'string',
            source: 'attribute',
            selector: '.custom-download-button',
            attribute: 'data-style',
            default: __('large', 'quick-download-button')
        },
        isSelectedLarge: {
            type: 'boolean',
            default: false
        },
        isSelectedSmall: {
            type: 'boolean',
            default: false
        },
        isSelectedMid: {
            type: 'boolean',
            default: false
        },
        isSelectedBasic: {
            type: 'boolean',
            default: false
        },
        buttonType : {
            type: 'string',
            source: 'attribute',
            selector: 'button'
        },
        downloadTitle : {
            type: 'string',
            source: 'text',
            selector: 'button',
            default: __('Download', 'quick-download-button')
        },
        downloadTitlePlaceholder : {
            type: 'string',
            source: 'attribute', 
            selector: 'button',
            attribute: 'title',
            default: __('Download', 'quick-download-button')
        },
        downloadPageId: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-page-id',
            default: qdbu_data.download_page_id
        },
        downloadAttachmentId: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-attachment-id'
        },
        attachementUrl: {
            type: 'string'
        },
        downloadFormat: {
            type: 'string',
            source: 'attribute',
            selector: 'p.up i',
            attribute: 'class',
            default: 'fi fi-file'
        },
        downloadFileSize: {
            type: "string",
            source: "text",
            selector: "p.down",
            default: __('File size', 'quick-download-button')
          },
        downloadWaitTime: {
            type: "string",
            source: 'attribute',
            selector: 'button',
            attribute: 'data-wait-time',
            default: 0
        },
        externalUrl: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-external-url',
        }, 
        waitDuration: {
            type: 'string',
            source: 'attritube',
            selector: 'button',
            attribute: 'data-wait-duration',
            default: "0"
        },
        waitMessage: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-msg',
            default: 'Please wait...'
        },
        spinnerValue: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-spinner'
        },
        useExternalLink: {
            type: Boolean,
            source: 'attribute',
            selector: 'button',
            attribute: 'ext',
            default: false
        },
        backgroundColor: {
            type: "string"
        },
        fontColor: {
            type: "string"
        },
        buttonBorderWidth: {
            type: "number",
            default: 1
        },
        buttonBorderColor: {
            type: "string",
            default: "#e2e2e2"
        },
        buttonBorderStyle: {
            type: "string",
            default: 'solid'
        },
        borderRadius: {
            type: "number",
            default: 25,
        },
        isSelectedDotted: {
            type: 'boolean',
            default: false
        },
        isSelectedSolid: {
            type: 'boolean',
            default: false
        },
        isSelectedNone: {
            type: 'boolean',
            default: false
        },
        haveExternal: {
            type: "boolean",
            default: false
        },
        targetBlank: {
            type: "boolean",
            default: false
        },
        hasDownloadIconDark: {
            type: "boolean",
            default: true
        },
        hasFileIcon: {
            type: "boolean",
            default: true
        },
        hasFileSize: {
            type: "boolean",
            default: true
        },
        haveManualTimer: {
            type: "boolean",
            default: false
        },
        pID: {
            type: 'string',
            source: 'attribute',
            selector: 'button',
            attribute: 'data-id'
        },
        iconDownload: {
            type: 'string',
            default: download_button_icon
        },
        align: {
            type: 'string',
            default: 'center'
        },
        role: {
			type: 'object',
		},
        user_role: {
            type: 'string',
            default: "0"
        },
        isSelectedPill: {
            type: 'boolean',
            default: false
        },
        isSelectedCard: {
            type: 'boolean',
            default: true
        },
        isSelectedGhost: {
            type: 'boolean',
            default: false
        },
        panelColor: {
            type: 'string'
        },
        iconId: {
            type: 'string',
            default: 'default'
        },
        customIconSvg: {
            type: 'string',
            default: ''
        },
        customFileTypeIcon: {
            type: 'string',
            default: ''
        },
        fileSizeIconId: {
            type: 'string',
            default: 'folder'
        },
        customFileSizeIconSvg: {
            type: 'string',
            default: ''
        },
        iconPosition: {
            type: 'string',
            default: 'left'
        },
        manualFileSize: {
            type: 'string',
            default: ''
        },
        popupEnabled: {
            type: 'boolean',
            default: false
        },
        popupContent: {
            type: 'string',
            default: ''
        },
        popupClosable: {
            type: 'boolean',
            default: true
        },
        btnId: {
            type: 'string',
            default: ''
        },

    },
    supports: {
        align: ['center', 'left', 'right'],
        className: true
    },
   

    edit: props => {

        const blockProps = useBlockProps();
        // Lift info from props and populate various constants.
        const {
            attributes : {  
                downloadTitle, 
                downloadFileSize, 
                downloadPageId, 
                downloadAttachmentId, 
                attachementUrl,
                downloadFormat, 
                downloadTitlePlaceholder, 
                haveExternal,
                haveManualTimer,
                targetBlank,
                hasDownloadIconDark,
                hasFileIcon,
                hasFileSize,
                externalUrl,
                waitDuration,
                waitMessage,
                buttonStyle,
                buttonType,
                isSelectedLarge,
                isSelectedSmall,
                isSelectedMid,
                isSelectedBasic,
                spinnerValue,
                backgroundColor,
                fontColor,
                buttonBorderWidth,
                buttonBorderColor,
                buttonBorderStyle,
                borderRadius,
                isSelectedDotted,
                isSelectedNone,
                isSelectedSolid,
                pID,
                iconDownload,
                role,
                user_role,
                align,
                isSelectedPill,
                isSelectedCard,
                isSelectedGhost,
                panelColor,
                iconId,
                customIconSvg,
                customFileTypeIcon,
                fileSizeIconId,
                customFileSizeIconSvg,
                iconPosition,
                manualFileSize,
                popupEnabled,
                popupContent,
                popupClosable,
                btnId,
            },
            setAttributes,
            className
        } = props;

        // Generate a stable random ID once on first insert
        useEffect( () => {
            if ( ! btnId ) {
                setAttributes( { btnId: Math.random().toString( 36 ).substr( 2, 9 ) } );
            }
        }, [] );


        const onChangeTitle = (newTitle) => {
            setAttributes( { downloadTitle: newTitle } );
            setAttributes ( {downloadTitlePlaceholder : newTitle} );
            setAttributes( { downloadWaitTime : '0' } );
            
        };

  

    
        const onMediaSelect = uploadObject => {
            //console.info('Media Info: ', uploadObject);
            setAttributes({ downloadFileSize: uploadObject.filesizeHumanReadable });
            let aid = parseInt(uploadObject.id)+parseInt(downloadPageId);
            let attachementUrl = uploadObject.url;
            setAttributes({ downloadAttachmentId: aid });
            setAttributes({ downloadPageId: qdbu_data.download_page_id }); 

            let fileExt = uploadObject.url.substr(uploadObject.url.lastIndexOf('.') + 1).trim();

            //Check if ext is image
            let imageExtension = ['jpg','jpeg','tiff','png','bmp','gif'];
            let foundExt = imageExtension.includes(fileExt.toLowerCase());

            if(foundExt === true) {
                let downloadExt = 'fi fi-image';
                setAttributes({ downloadFormat: downloadExt });
            } 

            //Check for other files
            let otherExtensions = ['pdf','mp3','mov','zip','txt','doc','xml','mp4','ppt','csv'];
            let foundOthers = otherExtensions.includes(fileExt.toLowerCase());

            if(foundOthers === true) {
                let extIndex = otherExtensions.indexOf(fileExt);
               
                let ext = otherExtensions[`${extIndex}`];

                let downloadExt = 'fi fi-'+ext;

                setAttributes({ downloadFormat: downloadExt });

            }

          }

          // Set newBackgroundColor
          const onChangeBackgroundColor = newBackgroundColor => {
            if(undefined === newBackgroundColor) {
                newBackgroundColor = '#FFFFFF';
            }
              setAttributes( { backgroundColor: newBackgroundColor });
          }

          const onChangeFontColor = value => {
                setAttributes( { fontColor: value });
          }

          const onChangePanelColor = value => {
                setAttributes( { panelColor: value } );
          }

          const onChangeBorderRadius = value => {
            setAttributes( { borderRadius: value });
          }

          const onChangeButtonBorder = value => {
            setAttributes(
                { buttonBorderStyle: value }
            );
            if(isSelectedNone === false) {
                setAttributes( { isSelectedSolid: false } ) 
                setAttributes( { isSelectedDotted: false } )
                setAttributes( { isSelectedNone: true } ) 
            } 
          }

          const onChangeBorderSolid = value => {
            setAttributes(
                { buttonBorderStyle: value }
            );
            if(isSelectedSolid === false) {
                setAttributes( { isSelectedSolid: true } ) 
                setAttributes( { isSelectedDotted: false } )
                setAttributes( { isSelectedNone: false } ) 
            } 
          }

          const onChangeBorderDotted = value => {
            setAttributes(
                { buttonBorderStyle: value }
            );
            if(isSelectedDotted === false) {
                setAttributes( { isSelectedSolid: false } ) 
                setAttributes( { isSelectedDotted: true } )
                setAttributes( { isSelectedNone: false } ) 
            } 
          }

          const selectedBorderStyle = () => {
            return isSelectedDotted ? 'dotted'
            : isSelectedNone ? 'none'
            : isSelectedSolid ? 'solid'
            : 'solid';
          }

          const isUrl = string => {
            var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
                '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
            return !!pattern.test(string);
         }
          const onChangeToggle = newValue => {
              setAttributes( { haveExternal: newValue });
              if(haveExternal === false) {
                  // reset url
                  setAttributes( {externalUrl: '' } ) 
              }
          }

        // Add manual time per seconds
        const onChangeToggleTimerManual = newValue => {
            setAttributes( { haveManualTimer: newValue });
        }

        const onTimerChange = newValue => {
            setAttributes(
                { spinnerValue: newValue } 
             )
        }

        // Target blank
        const onChangeToggleTargetBlank = newValue => {
            setAttributes( { targetBlank: newValue });
        }

          const onUrlChange = newValue => {
           setAttributes( {externalUrl: newValue } ) 
           if(isUrl(externalUrl)) {
             //updateMetaValue(externalUrl);
            }
          }

          const onMsgChange = newValue => {
              setAttributes(
                 { waitMessage: newValue } 
              )
          }

          const onChangeDownloadIconColor = value => {
            setAttributes( { hasDownloadIconDark: value} )
          }

          const onChangeHasFileIcon = value => {
            setAttributes( { hasFileIcon: value} )
          }

          const onChangeHasFileSize = value => {
            setAttributes( { hasFileSize: value} )
          }

          const onChangeButtonStyleLarge = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedLarge === false) {
                setAttributes( { isSelectedLarge: true } )
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedCard: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStyleSmall = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedSmall === false) {
                setAttributes( { isSelectedSmall: true } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedCard: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStyleMid = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedMid === false) {
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedMid: true } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedCard: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStyleBasic = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedBasic === false) {
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: true } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedCard: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStylePill = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedPill === false) {
                setAttributes( { isSelectedPill: true } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedCard: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStyleCard = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedCard === false) {
                setAttributes( { isSelectedCard: true } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedGhost: false } )
            }
          }

          const onChangeButtonStyleGhost = value => {
            setAttributes( { buttonStyle: value }, { buttonType: value } );
            if(isSelectedGhost === false) {
                setAttributes( { isSelectedGhost: true } )
                setAttributes( { isSelectedLarge: false } )
                setAttributes( { isSelectedSmall: false } )
                setAttributes( { isSelectedMid: false } )
                setAttributes( { isSelectedBasic: false } )
                setAttributes( { isSelectedPill: false } )
                setAttributes( { isSelectedCard: false } )
            }
          }


          const onRadioChange = newValue => {
            setAttributes(
               { spinnerValue: newValue } 
            )
        }

          const handleSubmit = (event) => {
            event.preventDefault();
          }

          
          const extUrl =  haveExternal
          ?
              <TextControl
              label={  __(`${isUrl(externalUrl) ? 'Enter URL (Url is valid)': 'Enter URL (*Provide a valid URL)'}`, 'quick-download-button') }
              help={ __( 'Don\'t use external URL if the file is located on your site. Double click on the download icon to upload file.', "quick-download-button") }
              value={ externalUrl }
              onChange={ 
                onUrlChange
              }
          /> :
          '';

          const durationnMsg = parseInt(spinnerValue)  > 0 ? 
          <TextControl
              label={  __(`Message to the user`, 'quick-download-button') }
              value={ waitMessage }
              placeholder={__("Please wait...", "quick-download-button")}
              onChange={ 
                onMsgChange
              }
          /> :
          '';

          const timerInput = haveManualTimer
          ? 
          <NumberControl
            isShiftStepEnabled={ true }
            shiftStep={ 1 }
            step={1}
            value={ parseInt(spinnerValue) }
            onChange={ 
                onTimerChange
            }
        /> : '';

        const buttonContent = <MediaUpload 
        onSelect={onMediaSelect}
        value={props.attributes.downloadUrl}
        render={({ open }) => (
            <Button
                className="custom-download-logo__button"
                onClick={open}
                icon={download_button_icon}
                showTooltip="true"
                label={__("Upload File.", "quick-download-button")}
            /> 
            )}
        />

        const displayFileSize = manualFileSize || downloadFileSize;

        const downButton = isSelectedLarge ?
        <p className="down" style={{background: backgroundColor}}>{ qdbRenderIcon( QDB_SIZE_ICONS, fileSizeIconId, customFileSizeIconSvg ) }
            <span className="file-size">{displayFileSize}</span>
        </p>
        :
        isSelectedMid ?
        <p className="down" style={{background: backgroundColor,  borderRadius: `0px ${borderRadius}px ${borderRadius}px 0px`}}>{ qdbRenderIcon( QDB_SIZE_ICONS, fileSizeIconId, customFileSizeIconSvg ) }
        <span className="file-size">{displayFileSize}</span>
        </p>
        :
        <p className="down" style={ (isSelectedPill || isSelectedCard || isSelectedGhost) && panelColor ? {background: panelColor} : {} }>{ qdbRenderIcon( QDB_SIZE_ICONS, fileSizeIconId, customFileSizeIconSvg ) }
            <span className="file-size">{displayFileSize}</span>
        </p>;

        const upButton = isSelectedLarge ?
        <p className="up" style={{background: backgroundColor}}>{ customFileTypeIcon ? <RawHTML>{ customFileTypeIcon }</RawHTML> : <i className={downloadFormat}></i> }
            { buttonContent }
        </p>
        : isSelectedMid ?
        <p className="up" style={{background: backgroundColor,  borderRadius: `${borderRadius}px 0px 0px ${borderRadius}px`}}>{ customFileTypeIcon ? <RawHTML>{ customFileTypeIcon }</RawHTML> : <i className={downloadFormat}></i> }
             { buttonContent }
        </p>
        :
        <p className="up" style={ (isSelectedPill || isSelectedCard || isSelectedGhost) && panelColor ? {background: panelColor} : {} }>{ customFileTypeIcon ? <RawHTML>{ customFileTypeIcon }</RawHTML> : <i className={downloadFormat}></i> }
             { buttonContent }
        </p>;

        const buttonIcon = isSelectedSmall ?  <span data-icon="icon-download-1"></span> : '';

        const [ item, setItem ] = useState( user_role );

        const onChangeRole = value => {
            setAttributes(
               { user_role: value },
              setItem( user_role, value ),
             // setItem( item, value )
            )
        }


        return [
      
		<BlockControls>
			
            <ToolbarGroup>
				
                <MediaUpload 
                    onSelect={onMediaSelect}
                    value={props.attributes.downloadUrl}
                    render={({ open }) => (
                        <Button
                          className="qdb-upload-media-button"
                          onClick={open}
                          icon={download_button_icon}
                          showTooltip="true"
                          label={__("Upload File.", "quick-download-button")}
                        /> 
                      )}
                />
				
			</ToolbarGroup>
		</BlockControls>,
            <InspectorControls>
                <PanelBody 
                    className="qdbnPanelBody"
                    initialOpen={true}
                    title= { __( 'Button Style', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <label className="components-base-control__label qdbu-editor-label">
                                { __("Select button style", "quick-download-button")}
                            </label>
                            <ToolbarGroup>
                                <ToolbarButton
                                    name='card'
                                    label='Card'
                                    text='Card'
                                    onClick={ () => onChangeButtonStyleCard('card') }
                                    isPressed= { isSelectedCard }
                                />
                                <ToolbarButton
                                    name='pill'
                                    label='Pill'
                                    text='Pill'
                                    onClick={ () => onChangeButtonStylePill('pill') }
                                    isPressed= { isSelectedPill }
                                />
                                <ToolbarButton
                                    name='ghost'
                                    label='Ghost'
                                    text='Ghost'
                                    onClick={ () => onChangeButtonStyleGhost('ghost') }
                                    isPressed= { isSelectedGhost }
                                />
                                <ToolbarButton
                                    name='large-qdb'
                                    label='Large'
                                    text='Large'
                                    onClick={ () => onChangeButtonStyleLarge('large') }
                                    isPressed= { isSelectedLarge  }
                                />
                                <ToolbarButton
                                    name='small-qdb'
                                    label='Small'
                                    text='Small'
                                    onClick={ () => onChangeButtonStyleSmall('small') }
                                    isPressed= { isSelectedSmall  }
                                />
                                 <ToolbarButton
                                    name='mid-qdb'
                                    label='Mid'
                                    text='Mid'
                                    onClick={ () => onChangeButtonStyleMid('mid') }
                                    isPressed= { isSelectedMid  }
                                />
                                <ToolbarButton
                                    name='basic'
                                    label='Basic'
                                    text='Basic'
                                    onClick={ () => onChangeButtonStyleBasic('basic') }
                                    isPressed= { isSelectedBasic  }
                                />
                            </ToolbarGroup>
                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={true}
                    title= { __( 'URL Settings', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                        
                        <ToggleControl
                            label= { __( 'Open link in new window?', "quick-download-button") } 
                            help={
                                targetBlank
                                    ?  __( 'Open the download link in a new window. This will try to open link in new tab when possible.', "quick-download-button") 
                                    : __( 'Open in same window. This will open link in same window when possible.', "quick-download-button") 
                            }
                            checked={ targetBlank }
                            onChange={ onChangeToggleTargetBlank }
                        />

                        <ToggleControl
                            label= { __( 'Use External URL?', "quick-download-button") } //"External URL"
                            help={
                                haveExternal
                                    ?  __( 'Use external URL.', "quick-download-button") //'Use external URL.'
                                    : __( 'Do not use External URL. Please double click the download icon to upload file from your site.', "quick-download-button") //'Do not use External URL.'
                            }
                            checked={ haveExternal }
                            onChange={ onChangeToggle }
                        />
                        { extUrl }
                        <TextControl
                            label={ __( 'Manual file size', 'quick-download-button' ) }
                            help={ __( 'Override auto-detected size, or set size for external files (e.g. 2.5 MB).', 'quick-download-button' ) }
                            value={ manualFileSize }
                            onChange={ val => setAttributes({ manualFileSize: val }) }
                            placeholder="e.g. 2.5 MB"
                        />
                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={true}
                    title= { __( 'Countdown Settings', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field qdbu-label-mtop">

                        <ToggleControl
                            label= { __( 'Manual', "quick-download-button") } // Enter time
                            help={
                                haveManualTimer
                                    ?  __( 'Enter timer manually.', "quick-download-button") //'Use external URL.'
                                    : __( 'Select one of the default timer durations.', "quick-download-button") //'Do not use External URL.'
                            }
                            checked={ haveManualTimer }
                            onChange={ onChangeToggleTimerManual }
                        />
                        { timerInput }

                        <RadioControl
                            label={ __( 'Timer', "quick-download-button") }
                            help={ __( 'The amount of time in seconds you want the user to wait before the download begins. Default is 0.', "quick-download-button") }
                            selected={ spinnerValue }
                            options={ [
                                { label: '0', value: '0', key: '0' },
                                { label: '10', value: '10', key: '10' },
                                { label: '15', value: '15', key: '15' },
                                { label: '20', value: '20', key: '20' },
                                { label: '25', value: '25', key: '25' },
                                { label: '30', value: '30', key: '30' },
                                { label: '60', value: '60', key: '60' },
                            ] }
                            onChange={ onRadioChange }
                        />
                        { durationnMsg }

                        <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />
                        <ToggleControl
                            label={ __( 'Show popup during countdown', 'quick-download-button' ) }
                            help={ popupEnabled
                                ? __( 'A modal popup will show while the countdown runs.', 'quick-download-button' )
                                : __( 'No popup. Requires a countdown timer > 0.', 'quick-download-button' ) }
                            checked={ popupEnabled }
                            onChange={ val => setAttributes({ popupEnabled: val }) }
                        />
                        { popupEnabled && (
                            <>
                            <ToggleControl
                                label={ __( 'Allow user to close popup', 'quick-download-button' ) }
                                help={ popupClosable
                                    ? __( 'User can dismiss the popup early. Download still starts when timer ends.', 'quick-download-button' )
                                    : __( 'Popup cannot be closed. It disappears only when the download starts.', 'quick-download-button' ) }
                                checked={ popupClosable }
                                onChange={ val => setAttributes({ popupClosable: val }) }
                            />
                            <TextareaControl
                                label={ __( 'Popup content', 'quick-download-button' ) }
                                help={ __( 'Enter HTML, shortcodes, or ad embed code to display inside the popup.', 'quick-download-button' ) }
                                value={ popupContent }
                                onChange={ val => setAttributes({ popupContent: val }) }
                                rows={ 5 }
                            />
                            </>
                        ) }
                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Button Background Color', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <label className="components-base-control__label qdbu-editor-label">
                                { __("Background color", "quick-download-button")}
                            </label>
                            <ColorPalette
                                value={props.backgroundColor}
                                onChange={onChangeBackgroundColor}
                                disableCustomColors={ false }
                                disableAlpha={ false }
                             />
                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Button Text Color', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <label className="components-base-control__label qdbu-editor-label">
                                { __("Text color", "quick-download-button")}
                            </label>
                            <ColorPalette
                                value={props.fontColor}
                                onChange={onChangeFontColor}
                                disableCustomColors={ false }
                                disableAlpha={ false }
                             />
                        </div>
                    </div>

                </PanelBody>
                { ( isSelectedPill || isSelectedCard || isSelectedGhost ) && (
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Panel Background Color', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <label className="components-base-control__label qdbu-editor-label">
                                { __("File type / file size panel color", "quick-download-button")}
                            </label>
                            <ColorPalette
                                value={ panelColor }
                                onChange={ onChangePanelColor }
                                disableCustomColors={ false }
                                disableAlpha={ false }
                             />
                        </div>
                    </div>
                </PanelBody>
                ) }
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Button Icon (Color / Show / Hide)', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <p>If using MID button style, please test the button on the frontend of your site. The icon shows up when you hover on the button.</p>
                        <ToggleControl
                            label= { __( 'Light (OR) Dark?', "quick-download-button") } 
                            help={
                                hasDownloadIconDark
                                    ?  __( 'Icon is DARK color. Using DARK color. Check that the button background is not set to dark/black color.', "quick-download-button") 
                                    : __( 'Icon is LIGHT color. When using LIGHT icon, check that the button background is not set to light/white color.', "quick-download-button") 
                            }
                            checked={ hasDownloadIconDark }
                            onChange={ onChangeDownloadIconColor }
                        />
                        <ToggleControl
                            label= { __( 'Show File Icon', "quick-download-button") } 
                            help={
                                hasFileIcon
                                    ?  __( 'File icon is visible', "quick-download-button") 
                                    : __( 'File icon is hidden.', "quick-download-button") 
                            }
                            checked={ hasFileIcon }
                            onChange={ onChangeHasFileIcon }
                        />
                        <ToggleControl
                            label= { __( 'Show File Size', "quick-download-button") }
                            help={
                                hasFileSize
                                    ?  __( 'File size is visible', "quick-download-button")
                                    : __( 'File size is hidden.', "quick-download-button")
                            }
                            checked={ hasFileSize }
                            onChange={ onChangeHasFileSize }
                        />
                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Button Border', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                           
                           <label className="components-base-control__label qdbu-editor-label">
								{__('Border Color', 'quick-download-button')}
							</label>
							<ColorPalette
								colors={colors}
								value={buttonBorderColor}
								onChange={(buttonBorderColor) =>
									setAttributes({ buttonBorderColor })
								}
							/>
                            <RangeControl
                                label={__('Border Width', 'quick-download-button')}
                                value={buttonBorderWidth}
                                onChange={(buttonBorderWidth) =>
                                    setAttributes({ buttonBorderWidth })
                                }
                                min={0}
                                max={3}
                            />
                            <label className="components-base-control__label qdbu-editor-label">
								{__('Border Style', 'quick-download-button')}
							</label>
                            <ToolbarGroup>
                                <ToolbarButton
                                    name='solid'
                                    label='Solid'
                                    text='Solid'
                                    onClick={  () => onChangeBorderSolid( 'solid' ) }
                                    isPressed= { isSelectedSolid  }
                                />
                                <ToolbarButton
                                    name='dotted'
                                    label='Dotted'
                                    text='Dotted'
                                    onClick={ () => onChangeBorderDotted( 'dotted' ) }
                                    isPressed= { isSelectedDotted  }
                                />
                                  <ToolbarButton
                                    name='none'
                                    label='None'
                                    text='None'
                                    onClick={ () => onChangeButtonBorder( 'none' )}
                                    isPressed= { isSelectedNone  }
                                />
                            </ToolbarGroup>
                            <RangeControl
                                    label={__('Border Radius', 'quick-download-button')}
                                    value={borderRadius}
                                    onChange={ onChangeBorderRadius }
                                    min={0}
                                    max={25}
                                />
                        </div>
                    </div>

                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title= { __( 'Condition', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <label className="components-base-control__label qdbu-editor-label">
                                { __("What condition user must met to download?", "quick-download-button")}
                            </label>
                            <SelectControl
                            label="Select user role / condition"
                            value={ user_role }
                            options={ 
                                user_roles.map((v, index) => (
                                    0 == index ?  {label: v, value: index } 
                                    : 
                                    1 == index ?  {label: v, value: 'loggedin' } 
                                    : {label: v, value: v }
                                ))
                            }
                            onChange={ onChangeRole  }
                            __nextHasNoMarginBottom
                        />
                           
                        </div>
                    </div>

                </PanelBody>
                <PanelBody className="qdbnPanelBody"
                    initialOpen={false}
                    title={ __( 'Icons', 'quick-download-button' ) }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">

                            <QdbIconPicker
                                icons={ QDB_DOWNLOAD_ICONS }
                                selected={ iconId }
                                customSvg={ customIconSvg }
                                onSelect={ val => setAttributes({ iconId: val }) }
                                onCustomSvgChange={ val => setAttributes({ customIconSvg: val }) }
                                label={ __( 'Download button icon', 'quick-download-button' ) }
                            />

                            <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />

                            <label className="components-base-control__label qdbu-editor-label">
                                { __( 'File type icon (auto-detected by default)', 'quick-download-button' ) }
                            </label>
                            <ToggleControl
                                label={ __( 'Use custom file type icon', 'quick-download-button' ) }
                                checked={ !! customFileTypeIcon }
                                onChange={ val => setAttributes({ customFileTypeIcon: val ? ( customFileTypeIcon || '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M6 2c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg>' ) : '' }) }
                            />
                            { !! customFileTypeIcon && (
                                <TextareaControl
                                    label={ __( 'Paste SVG code for file type icon', 'quick-download-button' ) }
                                    value={ customFileTypeIcon }
                                    onChange={ val => setAttributes({ customFileTypeIcon: val }) }
                                    rows={ 3 }
                                />
                            ) }

                            <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />

                            <QdbIconPicker
                                icons={ QDB_SIZE_ICONS }
                                selected={ fileSizeIconId }
                                customSvg={ customFileSizeIconSvg }
                                onSelect={ val => setAttributes({ fileSizeIconId: val }) }
                                onCustomSvgChange={ val => setAttributes({ customFileSizeIconSvg: val }) }
                                label={ __( 'File size panel icon', 'quick-download-button' ) }
                            />

                            <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />

                            <label className="components-base-control__label qdbu-editor-label">
                                { __( 'Download icon position', 'quick-download-button' ) }
                            </label>
                            <ToolbarGroup>
                                <ToolbarButton
                                    name="icon-left"
                                    label={ __( 'Left', 'quick-download-button' ) }
                                    text={ __( 'Left', 'quick-download-button' ) }
                                    onClick={ () => setAttributes({ iconPosition: 'left' }) }
                                    isPressed={ iconPosition === 'left' }
                                />
                                <ToolbarButton
                                    name="icon-right"
                                    label={ __( 'Right', 'quick-download-button' ) }
                                    text={ __( 'Right', 'quick-download-button' ) }
                                    onClick={ () => setAttributes({ iconPosition: 'right' }) }
                                    isPressed={ iconPosition === 'right' }
                                />
                            </ToolbarGroup>
                            { isSelectedMid && iconPosition === 'right' && (
                                <p style={{ fontSize: '12px', color: '#888', marginTop: '8px' }}>
                                    { __( 'Icon position has no effect on the Mid button style.', 'quick-download-button' ) }
                                </p>
                            ) }

                        </div>
                    </div>
                </PanelBody>
                <PanelBody className="qdbnPanelBody feedBack"
                    initialOpen={true}
                    title= { __( 'Feedback', "quick-download-button") }>
                    <div className="components-base-control">
                        <div className="component-base-control__field">
                            <div className='feedBackL1'>
                                <span>If you like Quick Download Button, </span>
                                <span>please leave us a <a href="https://wordpress.org/support/plugin/quick-download-button/reviews/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> review on WordPress.org!</span>
                            </div>
                        </div>
                    </div>
                </PanelBody>
                
               
            </InspectorControls>,
            <div className="qdbn-wrapper">
                <div className= {`${className} qdbn`} 
                    data-plugin-name="qdbn"
                    data-style={`${props.attributes.isSelectedLarge ? 'large'
                        : props.attributes.isSelectedSmall ? 'small'
                        : props.attributes.isSelectedBasic ? 'basic'
                        : props.attributes.isSelectedMid ? 'mid'
                        : props.attributes.isSelectedPill ? 'pill'
                        : props.attributes.isSelectedCard ? 'card'
                        : props.attributes.isSelectedGhost ? 'ghost'
                        : 'large' }`}
                    data-file={`${!props.attributes.hasFileIcon ? 'hide-file' : ''}`}
                    data-size={`${!props.attributes.hasFileSize ? 'hide-size' : ''}`}
                    data-icon-position={ iconPosition }>
                    <div className={`${haveExternal && !manualFileSize ? 'qdbn-download-button-inner ext-link': 'qdbn-download-button-inner'}`}
                        style={ (isSelectedPill || isSelectedCard || isSelectedGhost) && (isSelectedSolid || isSelectedDotted || isSelectedNone) ? { border: `${buttonBorderWidth}px ${ isSelectedDotted ? 'dotted' : isSelectedNone ? 'none' : 'solid' } ${buttonBorderColor}` } : {} }>
                        <button
                        type="button"
                        data-button-type={`${props.attributes.isSelectedLarge ? 'large'
                            : props.attributes.isSelectedSmall ? 'small'
                            : props.attributes.isSelectedBasic ? 'basic'
                            : props.attributes.isSelectedMid ? 'mid'
                            : props.attributes.isSelectedPill ? 'pill'
                            : props.attributes.isSelectedCard ? 'card'
                            : props.attributes.isSelectedGhost ? 'ghost'
                            : 'large' }`}
                        className="g-btn f-l"
                        style={{ backgroundColor: isSelectedSmall ? backgroundColor
                            : isSelectedBasic ? backgroundColor
                            : isSelectedMid ? backgroundColor
                            : isSelectedPill ? backgroundColor
                            : isSelectedCard ? backgroundColor
                            : isSelectedGhost ? backgroundColor
                            : '#FFFFFF',
                            color : fontColor,
                            borderRadius: `${borderRadius}px`,
                            border: (isSelectedPill || isSelectedCard || isSelectedGhost) ? undefined : `${buttonBorderWidth}px ${isSelectedDotted ? 'dotted': isSelectedNone ? 'none': isSelectedSolid ? 'solid': 'solid'} ${buttonBorderColor}`, }}
                        title={downloadTitlePlaceholder} 
                        data-attachment-id={downloadAttachmentId} 
                        data-page-id={downloadPageId}
                        data-post-id=""
                        data-have-external={ haveExternal}
                        data-external-url={`${props.attributes.haveExternal ? props.attributes.externalUrl : ''}`}
                        data-target={ targetBlank }
                        data-wait-duration={waitDuration}
                        data-spinner={spinnerValue}
                        data-msg={props.attributes.waitMessage}
                        data-member ={`${user_role}`}
                        data-id={pID}
                        data-has-icon-dark={hasDownloadIconDark}
                        onSubmit={handleSubmit}>
                            <span className='download-btn-icon'>{ qdbRenderIcon( QDB_DOWNLOAD_ICONS, iconId, customIconSvg ) }</span>
                        <RichText
                            tagName="span"
                            placeholder={__("Download", "quick-download-button")}
                            onChange= { onChangeTitle}
                            value= {downloadTitle}
                            allowedFormats={ [] }
                            />
                        </button>
                    
                    { upButton }
                    { downButton }
                </div>
                </div>
                <quick-download-button-info className="qdb-btn-info"></quick-download-button-info>
            </div>
       
        ]


       
    },
    save: props =>  {
          const blockProps = useBlockProps.save();
          const { attributes } = props;    
          
        return (
            <div className="qdbn-wrapper">
                <div className={`qdbn`}
                    data-plugin-name="qdbn"
                    data-style={`${attributes.isSelectedLarge ? 'large' : attributes.isSelectedSmall ? 'small' : attributes.isSelectedBasic ? 'basic' : attributes.isSelectedMid ? 'mid' : attributes.isSelectedPill ? 'pill' : attributes.isSelectedCard ? 'card' : attributes.isSelectedGhost ? 'ghost' : 'large' }`}
                    data-file={`${!attributes.hasFileIcon ? 'hide-file' : ''}`}
                    data-size={`${!attributes.hasFileSize ? 'hide-size' : ''}`}
                    data-icon-position={ attributes.iconPosition }>
                    <div className={`${attributes.haveExternal && !attributes.manualFileSize ? 'qdbn-download-button-inner ext-link': 'qdbn-download-button-inner'}`}
                        style={ (attributes.isSelectedPill || attributes.isSelectedCard || attributes.isSelectedGhost) && (attributes.isSelectedSolid || attributes.isSelectedDotted || attributes.isSelectedNone) ? { border: `${attributes.buttonBorderWidth}px ${ attributes.isSelectedDotted ? 'dotted' : attributes.isSelectedNone ? 'none' : 'solid' } ${attributes.buttonBorderColor}` } : {} }>
                        <button
                        type="button"
                        data-button-type={`${attributes.isSelectedLarge ? 'large'
                            : attributes.isSelectedSmall ? 'small'
                            : attributes.isSelectedBasic ? 'basic'
                            : attributes.isSelectedMid ? 'mid'
                            : attributes.isSelectedPill ? 'pill'
                            : attributes.isSelectedCard ? 'card'
                            : attributes.isSelectedGhost ? 'ghost'
                            : 'large' }`}
                        className="g-btn f-l"
                        style={{ backgroundColor: attributes.isSelectedSmall ? attributes.backgroundColor
                            : attributes.isSelectedBasic ? attributes.backgroundColor
                            : attributes.isSelectedMid ? attributes.backgroundColor
                            : attributes.isSelectedPill ? attributes.backgroundColor
                            : attributes.isSelectedCard ? attributes.backgroundColor
                            : attributes.isSelectedGhost ? attributes.backgroundColor
                            : '#FFFFFF',
                            color : attributes.fontColor,
                            borderRadius: `${attributes.borderRadius}px`,
                            border: (attributes.isSelectedPill || attributes.isSelectedCard || attributes.isSelectedGhost) ? undefined : `${attributes.buttonBorderWidth}px ${attributes.isSelectedDotted ? 'dotted': attributes.isSelectedNone ? 'none': attributes.isSelectedSolid ? 'solid': 'solid'} ${attributes.buttonBorderColor}`, }}
                        data-attachment-id={attributes.downloadAttachmentId} 
                        data-page-id={attributes.downloadPageId}
                        data-post-id=""
                        data-have-external={attributes.haveExternal}
                        data-external-url={`${attributes.haveExternal ? attributes.externalUrl : ''}`}
                        data-wait-duration={props.attributes.waitDuration}
                        data-target-blank={ attributes.targetBlank }
                        data-msg={attributes.waitMessage}
                        data-member ={`${attributes.user_role}`}
                        data-spinner={attributes.spinnerValue}
                        data-id={attributes.pID}
                        data-has-icon-dark={attributes.hasDownloadIconDark}
                        { ...(attributes.popupEnabled ? { 'data-qdb-popup': '1' } : {}) }
                        { ...(attributes.popupEnabled && !attributes.popupClosable ? { 'data-qdb-popup-closable': '0' } : {}) }
                        { ...(attributes.btnId ? { 'data-qdb-btn-id': attributes.btnId } : {}) }
                        title={attributes.downloadTitlePlaceholder}>
                            <span className='download-btn-icon'>{ qdbRenderIcon( QDB_DOWNLOAD_ICONS, attributes.iconId, attributes.customIconSvg ) }</span>
                            <RichText.Content tagName="span" value={attributes.downloadTitle} />
                        </button>
                        <p className="up" style={{
                            background: attributes.isSelectedLarge ? attributes.backgroundColor
                                : attributes.isSelectedMid ? attributes.backgroundColor
                                : (attributes.isSelectedPill || attributes.isSelectedCard || attributes.isSelectedGhost) ? (attributes.panelColor || undefined)
                                : undefined,
                            borderRadius: attributes.isSelectedMid ? `${attributes.borderRadius}px 0px 0px ${attributes.borderRadius}px` : '0'
                            }}>
                            { attributes.customFileTypeIcon ? <RawHTML>{ attributes.customFileTypeIcon }</RawHTML> : <i className={attributes.downloadFormat}></i> }
                        </p>
                        <p className="down" style={{
                            background: attributes.isSelectedLarge ? attributes.backgroundColor
                                : attributes.isSelectedMid ? attributes.backgroundColor
                                : (attributes.isSelectedPill || attributes.isSelectedCard || attributes.isSelectedGhost) ? (attributes.panelColor || undefined)
                                : undefined,
                            borderRadius: attributes.isSelectedMid ? `0px ${attributes.borderRadius}px ${attributes.borderRadius}px 0px` : '0'
                            }}> 
                            { qdbRenderIcon( QDB_SIZE_ICONS, attributes.fileSizeIconId, attributes.customFileSizeIconSvg ) }
                            <span className="file-size">{ attributes.manualFileSize || props.attributes.downloadFileSize }</span>
                        </p>  
                    </div>
                </div>
                <quick-download-button-info className="qdb-btn-info"></quick-download-button-info>
                { attributes.popupEnabled && attributes.popupContent && (
                    <div className="qdb-popup-src" hidden><RawHTML>{ attributes.popupContent }</RawHTML></div>
                ) }
            </div>
        )

    },
} );

registerBlockType( 'quick-download-button/button-row', {
    title: __( 'Download Button Row', 'quick-download-button' ),
    icon: blockIcon,
    description: __( 'Place 2–3 download buttons side by side on one line.', 'quick-download-button' ),
    category: 'widgets',
    keywords: [
        __( 'download', 'quick-download-button' ),
        __( 'button', 'quick-download-button' ),
        __( 'row', 'quick-download-button' ),
    ],
    attributes: {
        layout: {
            type: 'string',
            default: 'horizontal'
        },
        stackOnMobile: {
            type: 'boolean',
            default: true
        },
        alignment: {
            type: 'string',
            default: 'left'
        },
        gap: {
            type: 'number',
            default: 12
        }
    },

    edit: ( { attributes, setAttributes } ) => {
        const { layout, stackOnMobile, alignment, gap } = attributes;

        const rowJustify  = layout !== 'stack' ? ( alignMap[ alignment ] || 'flex-start' ) : undefined;
        const rowAlign    = layout === 'stack'  ? ( alignMap[ alignment ] || 'flex-start' ) : 'flex-start';

        return [
            <InspectorControls>
                <PanelBody
                    title={ __( 'Button Row Settings', 'quick-download-button' ) }
                    initialOpen={ true }
                >
                    <SelectControl
                        label={ __( 'Layout', 'quick-download-button' ) }
                        value={ layout }
                        options={ [
                            { label: __( 'Horizontal', 'quick-download-button' ), value: 'horizontal' },
                            { label: __( 'Stack', 'quick-download-button' ), value: 'stack' },
                        ] }
                        onChange={ ( layout ) => setAttributes( { layout } ) }
                    />
                    <ToggleControl
                        label={ __( 'Stack on mobile', 'quick-download-button' ) }
                        help={
                            stackOnMobile
                                ? __( 'Buttons stack vertically on small screens.', 'quick-download-button' )
                                : __( 'Buttons stay inline on all screen sizes.', 'quick-download-button' )
                        }
                        checked={ stackOnMobile }
                        onChange={ ( stackOnMobile ) => setAttributes( { stackOnMobile } ) }
                    />
                    <SelectControl
                        label={ __( 'Alignment', 'quick-download-button' ) }
                        value={ alignment }
                        options={ [
                            { label: __( 'Left', 'quick-download-button' ), value: 'left' },
                            { label: __( 'Center', 'quick-download-button' ), value: 'center' },
                            { label: __( 'Right', 'quick-download-button' ), value: 'right' },
                        ] }
                        onChange={ ( alignment ) => setAttributes( { alignment } ) }
                    />
                    <RangeControl
                        label={ __( 'Gap (px)', 'quick-download-button' ) }
                        value={ gap }
                        onChange={ ( gap ) => setAttributes( { gap } ) }
                        min={ 0 }
                        max={ 60 }
                    />
                </PanelBody>
            </InspectorControls>,
            <div
                className={ `qdb-btn-row qdb-btn-row--${ layout }${ stackOnMobile ? ' qdb-btn-row--mobile-stack' : '' } qdb-btn-row--align-${ alignment }` }
                style={ {
                    '--qdb-row-gap':     `${ gap }px`,
                    '--qdb-row-justify': rowJustify || 'flex-start',
                    '--qdb-row-align':   rowAlign,
                    gap:                 `${ gap }px`,
                    justifyContent:      rowJustify,
                    alignItems:          rowAlign,
                } }
            >
                <InnerBlocks
                    allowedBlocks={ [ 'quick-download-button/download-button' ] }
                    orientation="horizontal"
                    renderAppender={ InnerBlocks.ButtonBlockAppender }
                />
            </div>
        ];
    },

    save: ( { attributes } ) => {
        const { layout, stackOnMobile, alignment, gap } = attributes;
        const saveJustify = layout !== 'stack' ? ( alignMap[ alignment ] || 'flex-start' ) : undefined;
        const saveAlign   = layout === 'stack'  ? ( alignMap[ alignment ] || 'flex-start' ) : 'flex-start';

        return (
            <div
                className={ `qdb-btn-row qdb-btn-row--${ layout }${ stackOnMobile ? ' qdb-btn-row--mobile-stack' : '' } qdb-btn-row--align-${ alignment }` }
                style={ { gap: `${ gap }px`, justifyContent: saveJustify, alignItems: saveAlign } }
            >
                <InnerBlocks.Content />
            </div>
        );
    }
} );
