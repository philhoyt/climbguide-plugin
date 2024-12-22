/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

/**
 * External dependencies
 */
import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { restrictToVerticalAxis } from '@dnd-kit/modifiers';
import apiFetch from '@wordpress/api-fetch';
import { Notice, Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { SortableRoute } from './components/sortable-route';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param {Object}   root0
 * @param {Object}   root0.attributes
 * @param {Function} root0.setAttributes
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const [routes, setRoutes] = useState([]);
	const [isSaving, setIsSaving] = useState(false);

	// Get current post (area) ID
	const currentPostId = useSelect((select) => select('core/editor').getCurrentPostId());

	// Get area term, routes and route order
	const areaAndRoutes = useSelect(
		(select) => {
			const postType = select('core/editor').getCurrentPostType();
			if (postType !== 'climbing_area') {
				return null;
			}

			const post = select('core/editor').getCurrentPost();
			if (!post?.slug) {
				return null;
			}

			// Get area term
			const areaTerm = select('core').getEntityRecords('taxonomy', 'route_area', {
				slug: post.slug,
				per_page: 1,
			});

			if (!areaTerm?.[0]?.id) {
				return null;
			}

			// Get routes for this area
			const areaRoutes = select('core').getEntityRecords('postType', 'climbing_route', {
				route_area: areaTerm[0].id,
				per_page: -1,
				_embed: true,
			});

			// Get the route order from post meta
			const routeOrder = select('core/editor').getEditedPostAttribute('meta')?._route_order;

			return {
				term: areaTerm[0],
				routes: areaRoutes || [],
				routeOrder,
			};
		},
		[currentPostId]
	);

	// Update routes when data is loaded
	useEffect(() => {
		if (areaAndRoutes?.routes) {
			const orderedRoutes = [...areaAndRoutes.routes];

			// Apply custom order if it exists
			if (areaAndRoutes.routeOrder?.length) {
				orderedRoutes.sort((a, b) => {
					const aIndex = areaAndRoutes.routeOrder.indexOf(a.id);
					const bIndex = areaAndRoutes.routeOrder.indexOf(b.id);

					if (aIndex === -1) {
						return 1;
					}
					if (bIndex === -1) {
						return -1;
					}
					return aIndex - bIndex;
				});
			}

			setRoutes(orderedRoutes);
		}
	}, [areaAndRoutes]);

	// DnD sensors configuration
	const sensors = useSensors(
		useSensor(PointerSensor),
		useSensor(KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		})
	);

	// Handle DnD events
	async function handleDragEnd(event) {
		const { active, over } = event;

		if (active.id !== over.id) {
			const oldIndex = routes.findIndex((route) => route.id === active.id);
			const newIndex = routes.findIndex((route) => route.id === over.id);

			const newRoutes = arrayMove(routes, oldIndex, newIndex);
			setRoutes(newRoutes);

			// Save the new order to post meta
			setIsSaving(true);
			try {
				await apiFetch({
					path: `/wp/v2/climbing_area/${currentPostId}`,
					method: 'POST',
					data: {
						meta: {
							_route_order: newRoutes.map((route) => route.id),
						},
					},
				});
			} catch (error) {
				console.error('Failed to save route order:', error);
			}
			setIsSaving(false);
		}
	}

	if (!areaAndRoutes) {
		return (
			<div {...blockProps}>
				<p>{__('This block can only be used with Climbing Areas.', 'climb-guide')}</p>
			</div>
		);
	}

	if (!routes?.length) {
		return (
			<div {...blockProps}>
				<p>{__('No routes found in this area.', 'climb-guide')}</p>
			</div>
		);
	}

	return (
		<div {...blockProps}>
			{isSaving && (
				<Notice status="info" isDismissible={false}>
					<Spinner />
					{__('Saving route order…', 'climb-guide')}
				</Notice>
			)}
			<DndContext
				sensors={sensors}
				collisionDetection={closestCenter}
				onDragEnd={handleDragEnd}
				modifiers={[restrictToVerticalAxis]}
				disabled={isSaving}
			>
				<SortableContext
					items={routes.map((route) => ({ id: route.id }))}
					strategy={verticalListSortingStrategy}
				>
					<ul className="area-routes__list">
						{routes.map((route) => (
							<SortableRoute key={route.id} route={route} disabled={isSaving} />
						))}
					</ul>
				</SortableContext>
			</DndContext>
		</div>
	);
}
