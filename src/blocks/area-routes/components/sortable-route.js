import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

export function SortableRoute({ route }) {
	const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
		id: route.id,
	});

	const style = {
		transform: CSS.Transform.toString(transform),
		transition,
		opacity: isDragging ? 0.5 : 1,
	};

	return (
		<li ref={setNodeRef} style={style} className="area-routes__item" {...attributes}>
			<div className="area-routes__drag-handle" {...listeners}>
				⋮
			</div>
			<div className="area-routes__content">
				{route.featured_media ? (
					<div className="area-routes__thumbnail">
						<img
							src={route._embedded?.['wp:featuredmedia']?.[0]?.source_url}
							alt={route.title.rendered}
						/>
					</div>
				) : null}
				<h3 className="area-routes__title">{route.title.rendered}</h3>
			</div>
		</li>
	);
}
