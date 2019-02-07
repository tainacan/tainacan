/**
 * A very simple autocomplete component
 *
 * This is to replace the OOTB Gutenberg Autocomplete component because it is
 * currently broken as of v4.5.1.
 *
 * See Github issue: https://github.com/WordPress/gutenberg/issues/10542
 *
 * Note: The options array should be an array of objects containing labels and values; i.e.:
 *   [
 *     { value: 'first', label: 'First' },
 *     { value: 'second', label: 'Second' }
 *   ]
 */

// Load external dependency.
import { isEmpty } from 'lodash';

function SimpleAutocomplete( {
  label,
  id,
  value,
  onChange,
  options = [],
} ) {
  // Construct a unique ID for this block.
  const blockId = `simple-autocomplete-${ id }`;

  // Function to handle the onChange event.
  const onChangeValue = ( event ) => {
    console.log("onChangeValue")
    onChange( event.target.value );
  };

  // Return the block, but only if options were passed in.
	return ! isEmpty( options ) && (
    <div>
      { /* Label for the block. */ }
      <label for={ blockId }>
        { label }
      </label>

      { /* Input field. */ }
      <input
        list={ blockId }
        value={ value }
        onChange={ onChangeValue }
        onInput={ console.log("onInputValue")}
      />

      { /* List of all of the autocomplete options. */ }
      <datalist id={ blockId }>
        { options.map( ( option, index ) =>
          <option 
            value={ option.value }
            label={ option.label }/>
        ) }
      </datalist>
    </div>
	);
}

export default SimpleAutocomplete;
