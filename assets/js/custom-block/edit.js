const { createElement, Fragment } = wp.element;
const { useBlockProps } = wp.blockEditor;
const { getBlockType } = wp.blocks;
const { serverSideRender } = wp;
import createInspectorControls from './inspector-controls.js';
/**
 * Get Rendered Block wrapped in Block Editor Markup
 * @export
 * @param {string} blockTypeName The blocks name
 * @param {Object} attributes The blocks attributes
 * @param {string} setAttributes The block's setAttributes method
 * @return {Element} Element representing the block
 */
export default function blockPrototypeEdit(blockTypeName, attributes, setAttributes) {
    return createElement(Fragment, null,
        createInspectorControls(
            getBlockType(blockTypeName).attributes,
            attributes,
            setAttributes
        ),
        createElement(
            'p',
            useBlockProps(),
            createElement(
                serverSideRender,
                {
                    block: blockTypeName,
                    attributes: attributes
                }
            ))
    );
}
