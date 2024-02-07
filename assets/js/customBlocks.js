const { registerBlockType } = wp.blocks;
for (const customBlockType in customBlocksData) {
    registerBlockType(customBlocksData[customBlockType].name, customBlocksData[customBlockType]);
}
