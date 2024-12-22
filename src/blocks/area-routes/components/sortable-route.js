import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { useSelect } from '@wordpress/data';

export function SortableRoute({ route, disabled }) {
	const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
		id: route.id,
		disabled,
	});

	const difficulty = useSelect(
		(select) => {
			const terms = select('core').getEntityRecords('taxonomy', 'difficulty', {
				include: route.difficulty,
				per_page: 1,
			});
			return terms?.[0];
		},
		[route.difficulty]
	);

	const featuredImage = route._embedded?.['wp:featuredmedia']?.[0];
	const imageUrl =
		featuredImage?.source_url || featuredImage?.media_details?.sizes?.thumbnail?.source_url;

	const style = {
		transform: CSS.Transform.toString(transform),
		transition,
		opacity: isDragging ? 0.5 : 1,
		cursor: disabled ? 'default' : 'move',
	};

	return (
		<li
			className="area-routes__item"
			ref={setNodeRef}
			style={style}
			{...attributes}
			{...listeners}
		>
			<div className="area-routes__link">
				{imageUrl ? (
					<div className="area-routes__thumbnail">
						<img src={imageUrl} alt={featuredImage.alt_text || route.title.rendered} />
					</div>
				) : null}
				<div className="area-routes__content">
					<h3 className="area-routes__title">{route.title.rendered}</h3>
					{difficulty && (
						<span className="area-routes__difficulty">{difficulty.name}</span>
					)}
				</div>
			</div>
		</li>
	);
}
