import { createHash } from 'crypto';
import { statSync } from 'fs';
import { readdir, readFile, writeFile } from 'fs/promises';
import { join as joinPath } from 'path';
import process from 'process';

const escapeRegExp = ( string ) =>
	string.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );

const repository = JSON.parse( process.argv[ 2 ] );
const skippedDirectories = [ '.github', '.git' ];

// Every generated repository gets its own wp-env port block, derived from the repository name:
// deterministic across re-generations of the same repo, and collision-reducing (not unique --
// distinct names can hash to the same block; wp-env override files cover that case), so
// side-by-side `wp-env start`s rarely contend for the same host ports. Two ports per block
// (dev + tests; this template has no below-floor tier) let the modulus double the sibling
// plugin template's block count in the same 10000-29999 range, halving the collision rate.
const TEMPLATE_PORT_BASE = 8894;
const nameHash = parseInt(
	createHash( 'sha256' )
		.update( repository.name )
		.digest( 'hex' )
		.slice( 0, 8 ),
	16
);
const portBase = 10000 + 2 * ( nameHash % 10000 );

const traverseDirectory = async ( dirPath, callback ) => {
	if ( skippedDirectories.includes( dirPath ) ) {
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

	const templateFile = await readFile( filePath, 'utf-8' );
	let renderedTemplate = templateFile,
		replacements;

	const title = repository.custom_properties[ 'human-title' ];
	// A real URL is autolinked so the rendered README stays bare-URL-lint-clean; the placeholder is prose.
	const homepageUrl = repository.homepage
		? `<${ repository.homepage }>`
		: 'production URL not yet provisioned — set the repository homepage';
	if ( 'README.md' === filePath ) {
		replacements = {
			EXAMPLE_REPO_NAME: title,
			EXAMPLE_REPO_SLUG: repository.name,
			EXAMPLE_REPO_PROD_URL: homepageUrl,
		};
	} else {
		replacements = {
			// The generators lint themselves in the template repo but self-delete at generation,
			// so they also strip their own paths from the generated repository's lint scope.
			' .github/workflows/fill-in-scaffold.mjs': '',
			' .github/workflows/fill-in-scaffold-content.mjs': '',
			'A template repository for A8C Special Projects full-site builds.':
				repository.description ?? '',
			'A8CSP Project Template': title,
			'A8C\\SpecialProjects\\ProjectTemplate':
				'A8C\\SpecialProjects\\' +
				title.replaceAll( ' ', '' ).replace( 'A8CSP', '' ),
			'a8csp/project-template': 'a8csp/' + repository.name,
			'a8csp-project-template': repository.name,
			a8csp_template:
				repository.custom_properties[ 'php-globals-short-prefix' ],
			A8CSP_TEMPLATE:
				repository.custom_properties[
					'php-globals-short-prefix'
				].toUpperCase(),
		};
	}

	const replacementPattern = new RegExp(
		Object.keys( replacements )
			.sort( ( first, second ) => second.length - first.length )
			.map( escapeRegExp )
			.join( '|' ),
		'g'
	);

	renderedTemplate = renderedTemplate.replace(
		replacementPattern,
		( match ) => {
			const value = replacements[ match ];
			const renderedValue =
				filePath.endsWith( '.json' ) || filePath.endsWith( '.map' )
					? JSON.stringify( value ).slice( 1, -1 )
					: value;
			return renderedValue;
		}
	);

	// Port literals are bare numbers, so they replace only inside their known anchors: the
	// wp-env `"port":` keys, playwright's `localhost:` base URL, and any backtick-wrapped mention
	// (the READMEs' ports table and prose both wrap the number in backticks) -- a tree-wide bare
	// `8894` would also match inside package-lock.json integrity hashes. The pass runs outside the
	// map above: its values are JSON-escaped when landing in .json files, which would corrupt a
	// bare numeric match.
	renderedTemplate = renderedTemplate.replace(
		/(?<="port": |localhost:|`)889[45](?=[,'\s|`])/g,
		( match ) =>
			String( portBase + ( Number( match ) - TEMPLATE_PORT_BASE ) )
	);

	renderedTemplate = renderedTemplate.replace( /[ \t]+$/gm, '' );

	if ( renderedTemplate !== templateFile ) {
		console.log( 'Changes were made. Overwriting file.' );
		await writeFile( filePath, renderedTemplate );
	}
};

await traverseDirectory( '.', buildTemplate );
