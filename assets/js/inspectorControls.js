const {
    PanelBody,
    TextControl,
    ToggleControl,
    __experimentalToggleGroupControl,
    __experimentalToggleGroupControlOption,
    RangeControl,
    __experimentalNumberControl,
    ComboboxControl,
    CheckboxControl,
    RadioControl,
    ToolsPanel,
    SelectControl
} = wp.components;
const {
    RichText,
    BlockControls,
    InspectorControls,
    InnerBlocks
} = wp.blockEditor;

const { createElement } = React;

/**
 * Get editor controls element to set Block-attributes
 * @export
 * @param {Object} typeAttributes of the BlockType
 * @param {Object} attributes of a custom BlockType
 * @param {function} setAttributes the block's setAttributes method
 * @return {InspectorControls} InspectorControls containing the Panel with Controls
 */
export default function createInspectorControls(typeAttributes, attributes, setAttributes) {
    const controls = [];
    for (const attributeName in attributes) {
        /* set attribute up */
        const attribute = Object.assign(typeAttributes[attributeName],{
            name: attributeName,
            label: attributeName.replace(/([a-z0-9])([A-Z])/g, '$1 $2'),
            value: attributes[attributeName]
        });
        /* if we have a fixed amount of possible values */
        if (undefined !== attribute.enum) {
            controls.push(
                createSelectControl(attribute, setAttributes)
            );
            continue;
        }

        switch (attribute.type) {
            case 'boolean':
                controls.push(
                    createBooleanControl(attribute, setAttributes)
                );
                break;

            case 'string':
                controls.push(
                    createStringControl(attribute, setAttributes)
                );
                break;

            case 'integer':
                controls.push(
                    createNumberControl(attribute, setAttributes)
                );
                break;

            case 'number':
                controls.push(
                    createNumberControl(attribute, setAttributes)
                );
                break;

            case 'object':

            case 'array':

            default:
        }
    }
    return createElement(
        InspectorControls, null,
        createElement(
            PanelBody,
            { title: 'Settings' },
            controls)
    );
}

function createStringControl(attribute, setAttributes) {
    attribute.source = attribute.source ? attribute.source : '';
    return createElement(
        TextControl, {
        label: attribute.label,
        value: attribute.value,
            onChange: (newValue) => {
                console.log({ [attribute.name]: newValue });
            setAttributes({ [attribute.name]: newValue })
        },
    });
}

function createBooleanControl(attribute, setAttributes) {
    return createElement(
        ToggleControl,
        {
            label: attribute.label,
            value: attribute.value,
            onChange: (newValue) => {
                const newAttribute = {};
                newAttribute[attribute.name] = newValue;
                setAttributes(newAttribute)
            },
        });
}

function createSelectControl(attribute, setAttributes) {
    attribute.source = attribute.source ? attribute.source : '';
    const options = [];
    for (const option in attribute.enum) {
        if (typeof attribute.enum[option] != 'string' &&
            typeof attribute.enum[option] != 'boolean' &&
            typeof attribute.enum[option] != 'number') {
            console.error(`Unsupported type in enum array: ${typeof attribute.enum[option]}`);
            continue;
        }
        options.push(
            {
                value: attribute.enum[option],
                label: attribute.enum[option].toString().replace(/([a-z0-9])([A-Z])/g, '$1 $2')
            }
        )
    }

    // many many options..
    if (options.length > 10) {
        return createElement(
            ComboboxControl, {
            label: attribute.label,
            value: attribute.value,
            options: options,
            onChange: (newValue) => {
                const newAttribute = {};
                newAttribute[attribute.name] = newValue;
                setAttributes(newAttribute)
            },
        });
    }

    // not so many options
    if (options.length < 6) {
        const properties = {};
        // Check if every of the labels is less than 5 characters long
        if (options.every(option => option.label.length <= 4)) {
            properties.isAdaptiveWidth = true;
        }
        // if previous check failed and all property names combined length is not longer than 50 characters
        if (Object.keys(properties).length === 0 &&
            options.map(option => option.label).join('').length <= 20)
        {
            properties.isAdaptiveWidth = false;
        }
        // if one of both was working out
        if (Object.keys(properties).length > 0) {
            const fields = [];
            for (const option in options) {
                fields.push(createElement(
                    __experimentalToggleGroupControlOption,
                    {
                        label: options[option].label,
                        value: options[option].value,
                    }
                ));
            }
            properties.label = attribute.label;
            properties.isBlock = true;
            properties.onChange = (newValue) => {
                const newAttribute = {};
                newAttribute[attribute.name] = newValue;
                setAttributes(newAttribute)
            }
            return createElement(__experimentalToggleGroupControl,
                properties,
                fields);
        }
        // fallback
        return createElement(
            SelectControl, {
            label: attribute.label,
            value: attribute.value,
            options: options,
            onChange: (newValue) => {
                const newAttribute = {};
                newAttribute[attribute.name] = newValue;
                setAttributes(newAttribute)
            },
        });
    }
}

function createNumberControl(attribute, setAttributes) {
    attribute.source = attribute.source ? attribute.source : '';
    attribute.min = attribute.min ? attribute.min : -Infinity;
    attribute.max = attribute.max ? attribute.max : Infinity;
    if (attribute.min == -Infinity || attribute.max == Infinity) {
        return createElement(
            __experimentalNumberControl, {
            label: attribute.label,
            value: attribute.value,
            min: attribute.min,
            max: attribute.max,
            onChange: (newValue) => {
                const newAttribute = {};
                newAttribute[attribute.name] = newValue;
                setAttributes(newAttribute)
            },
        });
    }
    return createElement(
        RangeControl, {
        label: attribute.label,
        value: attribute.value,
        min: attribute.min,
        max: attribute.max,
        onChange: (newValue) => {
            const newAttribute = {};
            newAttribute[attribute.name] = newValue;
            setAttributes(newAttribute)
        },
    });

}


/* 

null
boolean
object
array
string
integer
number (same as integer)

– (no value) – when no source is specified then data is stored in the block’s comment delimiter.
– attribute – data is stored in an HTML element attribute.
– text – data is stored in HTML text.
– html – data is stored in HTML. This is typically used by RichText.
– query – data is stored as an array of objects.

<InspectorControls>
    <PanelBody title={__('Settings', 'copyright-date-block')}>
        <ToggleControl
            checked={!!showStartingYear}
            label={__(
                'Show starting year',
                'copyright-date-block'
            )}
            onChange={() =>
                setAttributes({
                    showStartingYear: !showStartingYear,
                })
            }
        />
        {showStartingYear && (
            <TextControl
                label={__(
                    'Starting year',
                    'copyright-date-block'
                )}
                value={startingYear || ''}
                onChange={(value) =>
                    setAttributes({ startingYear: value })
                }
            />
        )}
    </PanelBody>
</InspectorControls> */