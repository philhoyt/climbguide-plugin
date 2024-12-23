import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<InnerBlocks template={metadata.template} templateLock="all" />
		</div>
	);
}
