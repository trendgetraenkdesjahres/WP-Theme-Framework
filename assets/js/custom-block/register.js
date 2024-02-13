const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { useBlockProps } = wp.blockEditor;
const { serverSideRender } = wp;
import blockPrototypeEdit from './edit.js';
import blockPrototypeSave from './save.js';

for (const blockTypeName in customBlocksData) {
    const block = customBlocksData[blockTypeName];

    const blockEdit = blockPrototypeEdit;

    block.edit = ({ attributes, setAttributes }) => (
        blockEdit(block.name, attributes, setAttributes)
    );
    /* block.save = () => (blockSave()); */
    registerBlockType(block.name, block);
}

/* {
    "name": "test/my-test-block",
        "isSelected": false,
            "attributes": {
        "content": "",
            "show": true,
                "choose one number": 1,
                    "choose a string": "hallo",
                        "choose a 2nd string": "okk",
                            "choose a 4nd string": "hallo",
                                "number": 3
    },
    "clientId": "8a9923e9-c5e5-4f7d-9be3-d339fcbbe23e",
        "isSelectionEnabled": true,
            "__unstableLayoutClassNames": "",
                "__unstableParentLayout": {
        "type": "default"
    },
    "context": "edit",
        "insertBlocksAfter",
        "mergeBlocks",
        "onRemove",
        "onReplace",
        "setAttributes",
        "toggleSelection",
} */
