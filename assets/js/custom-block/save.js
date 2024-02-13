const { createElement } = wp.element;
const { useBlockProps, InnerBlocks } = wp.blockEditor;
const { serverSideRender } = wp;
/**
 * Get Rendered Block wrapped in Block Editor Markup
 * @export
 * @param {string} blockTypeName The blocks name
 * @param {Object} attributes The blocks attributes
 * @return {Element} Element representing the block
 */
export default function blockPrototypeSave(blockTypeName, attributes) {
    return (createElement(InnerBlocks.Content, null));
}
