import { statSync } from 'fs';
import { readdir, readFile } from 'fs/promises';
import { writeFile } from 'fs/promises';
import { join as joinPath } from 'path';
import process from 'process';

const escapeRegExp = ( string ) => string.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );

const repository = JSON.parse( process.argv[2] );
const skip_dirs = [ '.github', '.git' ];

const traverseDirectory = async ( dirPath, callback ) => {
	if ( skip_dirs.includes( dirPath ) ) {
		console.log( 'Skipping %s', dirPath );
		return;
	}
	console.log( 'Traversing %s', dirPath );

	const files = await readdir( dirPath );
	for ( const file of files ) {
		const filePath = joinPath( dirPath, file );

		if ( statSync( filePath ).isFile() ) {
			await callback( filePath );
		} else {
			await traverseDirectory( filePath, callback );
		}
	}
};

const buildTemplate = async ( filePath ) => {
	if ( [ 'composer.lock', 'package-lock.json' ].includes( filePath ) ) {
		console.log( 'Skipping %s', filePath );
		return;
	}

	console.log( 'Building %s', filePath );

	const templateFile   = await readFile( filePath, 'utf-8' );
	let renderedTemplate = templateFile, replacements;

	const title = repository.custom_properties['human-title'];
	const homepageUrl = repository.homepage || 'production URL not yet provisioned — set the repository homepage';
	if ( 'README.md' === filePath ) {
		replacements = {
			'EXAMPLE_REPO_NAME': title,
			'EXAMPLE_REPO_SLUG': repository.name,
			'EXAMPLE_REPO_PROD_URL': homepageUrl,
		};
	} else {
		replacements = {
			'A template repository for A8C Special Projects full-site builds.': repository.description ?? '',
			'A8CSP Project Template': title,
			'A8C\\SpecialProjects\\ProjectTemplate': 'A8C\\SpecialProjects\\' + title.replaceAll( ' ', '' ).replace( 'A8CSP', '' ),
			'a8csp/project-template': 'a8csp/' + repository.name,
			'a8csp-project-template': repository.name,
			'a8csp_template': repository.custom_properties['php-globals-short-prefix'],
			'A8CSP_TEMPLATE': repository.custom_properties['php-globals-short-prefix'].toUpperCase(),
		};
	}

	const replacementPattern = new RegExp(
		Object.keys( replacements )
			.sort( ( first, second ) => second.length - first.length )
			.map( escapeRegExp )
			.join( '|' ),
		'g'
	);

	renderedTemplate = renderedTemplate.replace( replacementPattern, ( match ) => {
		const value = replacements[ match ];
		const renderedValue = filePath.endsWith( '.json' ) || filePath.endsWith( '.map' )
			? JSON.stringify( value ).slice( 1, -1 )
			: value;
		return renderedValue;
	} );

	renderedTemplate = renderedTemplate.replace( /[ \t]+$/gm, '' );

	if ( renderedTemplate !== templateFile ) {
		console.log( 'Changes were made. Overwriting file.' );
		await writeFile( filePath, renderedTemplate );
	}
};

await traverseDirectory( '.', buildTemplate );
