import { registerBlockType } from '@wordpress/blocks'; 
import { SVG, Path } from '@wordpress/primitives';
import { __ } from '@wordpress/i18n';
import { 
    ColorPalette, 
    InspectorControls, 
    MediaUpload, 
    BlockControls,
    useBlockProps,
    RichText
} from '@wordpress/block-editor';
import { 
    Button, 
    PanelBody, 
    TextControl, 
    ToggleControl, 
    RadioControl, __experimentalNumberControl as NumberControl,
	ToolbarGroup,
    ToolbarButton,
    RangeControl,
    SelectControl
} from '@wordpress/components';

import { useState } from '@wordpress/element';

import colors from './colors';

const download_button_icon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
		<Path d="M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z" />
	</SVG>
);

const blockIcon = <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="256.000000pt" height="256.000000pt" viewBox="0 0 256.000000 256.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,256.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M622 1807 c-454 -454 -462 -463 -462 -502 0 -39 8 -48 534 -574 l534 -534 43 6 c41 5 65 28 556 519 283 282 521 526 529 541 33 61 32 62 -449 545 -249 248 -455 452 -459 452 -5 0 -7 -208 -6 -462 l3 -463 222 -5 223 -5 -302 -249 c-166 -137 -307 -249 -313 -249 -6 0 -149 112 -318 249 l-306 249 219 3 220 2 0 470 c0 259 -1 470 -3 470 -1 0 -210 -208 -465 -463z"/></g></svg>;

let user_roles = qdbu_data['qdbn_user_roles'];


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
            default: true
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
        }

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
                align
            },
            setAttributes,
            className
        } = props;


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

            setAttributes(
                { buttonStyle: value },
                { buttonType: value }
            );
            if(isSelectedLarge === false) {
                setAttributes( { isSelectedLarge: true } ) 
                setAttributes( { isSelectedSmall: false } ) 
                setAttributes( { isSelectedMid: false } ) 
                setAttributes( { isSelectedBasic: false } ) 
            }  
          }

          const onChangeButtonStyleSmall = value => {
            setAttributes(
                { buttonStyle: value },
                { buttonType: value }
            );
            
            if(isSelectedSmall === false) {
                setAttributes( { isSelectedSmall: true } ) 
                setAttributes( { isSelectedLarge: false } ) 
                setAttributes( { isSelectedMid: false } ) 
                setAttributes( { isSelectedBasic: false } ) 
            } 
           
          }

          const onChangeButtonStyleMid = value => {
            setAttributes(
                { buttonStyle: value },
                { buttonType: value }
            );
            
            if(isSelectedMid === false) {
                setAttributes( { isSelectedSmall: false } ) 
                setAttributes( { isSelectedLarge: false } ) 
                setAttributes( { isSelectedBasic: false } ) 
                setAttributes( { isSelectedMid: true } ) 
            } 
           
          }

          const onChangeButtonStyleBasic = value => {
            setAttributes(
                { buttonStyle: value },
                { buttonType: value }
            );
            
            if(isSelectedBasic === false) {
                setAttributes( { isSelectedSmall: false } ) 
                setAttributes( { isSelectedLarge: false } ) 
                setAttributes( { isSelectedMid: false } ) 
                setAttributes( { isSelectedBasic: true } ) 
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

        const downButton = isSelectedLarge ?
        <p className="down" style={{background: backgroundColor}}><i className="fi-folder-o"></i>
            <span className="file-size">{downloadFileSize}</span>
        </p>
        :
        isSelectedMid ?
        <p className="down" style={{background: backgroundColor,  borderRadius: `0px ${borderRadius}px ${borderRadius}px 0px`}}><i className="fi-folder-o"></i>
        <span className="file-size">{downloadFileSize}</span>
        </p> 
        :
        <p className="down"><i className="fi-folder-o"></i>
            <span className="file-size">{downloadFileSize}</span>
        </p>;

        const upButton = isSelectedLarge ?
        <p className="up" style={{background: backgroundColor}}><i className={downloadFormat}></i> 
            { buttonContent }
        </p>
        : isSelectedMid ?
        <p className="up" style={{background: backgroundColor,  borderRadius: `${borderRadius}px 0px 0px ${borderRadius}px`}}><i className={downloadFormat}></i> 
             { buttonContent }
        </p>
        :
        <p className="up"><i className={downloadFormat}></i> 
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
                        : props.attributes.isSelectedMid ? 'mid' : 'large' }`}
                    data-file={`${!props.attributes.hasFileIcon ? 'hide-file' : ''}`}
                    data-size={`${!props.attributes.hasFileSize ? 'hide-size' : ''}`}>
                    <div className={`${haveExternal ? 'qdbn-download-button-inner ext-link': 'qdbn-download-button-inner'}`}>
                        <button 
                        type="button"
                        data-button-type={`${props.attributes.isSelectedLarge ? 'large' 
                            : props.attributes.isSelectedSmall ? 'small' 
                            : props.attributes.isSelectedBasic ? 'basic'
                            : props.attributes.isSelectedMid ? 'mid' : 'large' }`}
                        className="g-btn f-l" 
                        style={{ backgroundColor:  isSelectedSmall ?  backgroundColor 
                            : isSelectedBasic ?  backgroundColor 
                            : isSelectedMid ? backgroundColor : '#FFFFFF', 
                            color : fontColor, 
                            borderRadius: `${borderRadius}px`,
                            border: `${buttonBorderWidth}px ${isSelectedDotted ? 'dotted': isSelectedNone ? 'none': isSelectedSolid ? 'solid': 'solid'} ${buttonBorderColor}`, }}
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
                            <span className='download-btn-icon'>{iconDownload}</span>
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
                    data-style={`${attributes.isSelectedLarge ? 'large' : attributes.isSelectedSmall ? 'small' : attributes.isSelectedBasic ? 'basic' : attributes.isSelectedMid ? 'mid' : 'large' }`}
                    data-file={`${!attributes.hasFileIcon ? 'hide-file' : ''}`}
                    data-size={`${!attributes.hasFileSize ? 'hide-size' : ''}`}>
                    <div className={`${attributes.haveExternal ? 'qdbn-download-button-inner ext-link': 'qdbn-download-button-inner'}`}>
                        <button 
                        type="button" 
                        data-button-type={`${attributes.isSelectedLarge ? 'large' 
                            : attributes.isSelectedSmall ? 'small' 
                            : attributes.isSelectedBasic ? 'basic'
                            : attributes.isSelectedMid ? 'mid' : 'large' }`}
                        className="g-btn f-l" 
                        style={{ backgroundColor:  attributes.isSelectedSmall ?  attributes.backgroundColor 
                            : attributes.isSelectedBasic ?  attributes.backgroundColor
                            : attributes.isSelectedMid ? attributes.backgroundColor : '#FFFFFF', 
                            color : attributes.fontColor, 
                            borderRadius: `${attributes.borderRadius}px`, 
                            border: `${attributes.buttonBorderWidth}px ${attributes.isSelectedDotted ? 'dotted': attributes.isSelectedNone ? 'none': attributes.isSelectedSolid ? 'solid': 'solid'} ${attributes.buttonBorderColor}`, }}
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
                        title={attributes.downloadTitlePlaceholder}>
                            <span className='download-btn-icon'>{attributes.iconDownload}</span>
                            <RichText.Content tagName="span" value={attributes.downloadTitle} />
                        </button>
                        <p className="up" style={{ 
                            background:  attributes.isSelectedLarge ?  attributes.backgroundColor : attributes.isSelectedMid ? attributes.backgroundColor  : 'transparent',
                            borderRadius: attributes.isSelectedMid ? `${attributes.borderRadius}px 0px 0px ${attributes.borderRadius}px` : '0'
                            }}>                  
                            <i className={attributes.downloadFormat}></i> 
                        </p>
                        <p className="down" style={{ 
                            background:  attributes.isSelectedLarge ?  attributes.backgroundColor : attributes.isSelectedMid ? attributes.backgroundColor  : 'transparent',
                            borderRadius: attributes.isSelectedMid ? `0px ${attributes.borderRadius}px ${attributes.borderRadius}px 0px` : '0'
                            }}> 
                            <i className="fi-folder-o"></i>
                            <span className="file-size">{props.attributes.downloadFileSize}</span>
                        </p>  
                    </div>
                </div>
                <quick-download-button-info className="qdb-btn-info"></quick-download-button-info>
            </div>
        )
        
    },
} );
