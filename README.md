# VIP Beer Finder

Integrate your VIP beer finder with a WordPress shortcode.

## Example usage

```html
[beer-finder id="Your custID" geolocate="true"] 
```

## Available options

- 'id'        => 'VIP',
- 'territory' => false,
- 'height'    => '560',
- 'width'     => '100%',
- 'zip'       => false,
- 'miles'     => false,
- 'brand'     => false,
- 'address'   => false, // address to pre-fill (when search by address enabled)
- 'theme'     => 'bs-paper',
    - 'bs-cosmo'
    - 'bs-cyborg'
    - 'bs-darkly'
    - 'bs-flatly'
    - 'bs-journal'
    - 'bs-simplex'
    - 'bs-slate'
    - 'bs-united'
- 'pagesize'  => false, // Default '50'
- 'geolocate' => false,
