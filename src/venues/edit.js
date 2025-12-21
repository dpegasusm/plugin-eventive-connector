/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit() {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps } className="provincetown-agile-login" style={ { textAlign: 'center', padding: '1em' } }>
            <div style={ { width: '50px', height: '50px', display: 'inline-flex', justifyContent: 'center', alignItems: 'center', backgroundColor: '#f0f0f0', borderRadius: '4px' } }>
                <span className="dashicons dashicons-admin-users" style={ { fontSize: '24px', color: '#555' } }></span>
            </div>
        </div>
    );
}