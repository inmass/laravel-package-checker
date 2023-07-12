<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package List</title>
    @csrf

    <!-- Add Bootstrap for table styling (optional) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Package List</h1>
        <h4>This is a list of all the packages required in your project's composer.json file.</h4>
        <p>You can also see every installed package on your vendor from the <a href="#chart_div">chart below</a>.</p>

        <div class="mb-4">
            <p>Package Statuses:</p>
            <span class="badge badge-success">Good</span>
            <span class="badge badge-warning">Bad</span>
            <span class="badge badge-danger">Worse</span>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Package Name</th>
                    <th scope="col">Package Requirements</th>
                    <th scope="col">Version</th>
                    <th scope="col">Latest Version</th>
                    <th scope="col">Status</th>
                    <th scope="col">Release Date</th>
                    <th scope="col">Size</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($installedPackages as $package)
                    <tr>
                        <td>{{ $package['name'] }}</td>
                        <td>
                            @foreach ($package['requirements'] as $requirement)
                                <span class="badge badge-primary">{{ $requirement }}</span>
                            @endforeach
                        </td>
                        <td>{{ $package['version'] }}</td>
                        <td>{{ $package['latest_version'] }}</td>
                        <td>
                            {{-- just a colored small circle (good, bad, worse)--}}
                            @if ($package['status'] === 'good')
                                <span class="badge badge-success">Good</span>
                            @elseif ($package['status'] === 'bad')
                                <span class="badge badge-warning">Bad</span>
                            @elseif ($package['status'] === 'worse')
                                <span class="badge badge-danger">Worse</span>
                            @else
                                {{ $package['status'] }}
                            @endif

                        </td>
                        <td>{{ $package['release_date'] }}</td>
                        <td class="package-size" data-package-name="{{ $package['name'] }}" data-package-requirements="{{ json_encode($package['requirements']) }}">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>

    </div>
    <div id="chart_div" style="width: 100%; height: 1000px;"></div>
</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script>
    jQuery(document).ready(function($) {
        jQuery('.package-size').each(function() {
            var packageName = jQuery(this).data('package-name');
            var packageRequirements = jQuery(this).data('package-requirements');
            var packageSize = jQuery(this);

            let payload = {
                name: packageName,
                requirements: packageRequirements,
            };

            jQuery.ajax({
                url: "{{ route('package-checker.get-size') }}", // "http://localhost:8000/package-checker/get-size/?name=" + packageName,
                method: 'POST',
                data: payload,
                success: function(response) {
                    packageSize.html(response);
                }
            });
        });
    });
</script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/treemap.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script type="text/javascript">

    var url = "{{ route('package-checker.get-vendor-size') }}";
    let vendorTotalSize = 0;
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            response.forEach(function(item) {
                if (item.value) {
                    vendorTotalSize += item.value;
                }
            });
            vendorTotalSize = formatSizeUnits(vendorTotalSize);
            drawChart(response);
        }
    });

    function formatSizeUnits(bytes) {
        if (bytes === 0) {
            return '0 B';
        }
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let i = 0;

        while (bytes >= 1024 && i < units.length - 1) {
            bytes /= 1024;
            i++;
        }

        return Math.round(bytes * 100) / 100 + ' ' + units[i];
    }

    function drawChart(data) {
        Highcharts.chart('chart_div', {
        series: [{
            type: 'treemap',
            layoutAlgorithm: 'stripes',
            alternateStartingDirection: true,
            borderColor: '#fff',
            borderRadius: 0,
            borderWidth: 2,
            dataLabels: {
                style: {
                    textOutline: 'none'
                }
            },
            levels: [{
                level: 1,
                layoutAlgorithm: 'squarified',
                dataLabels: {
                    enabled: true,
                    align: 'left',
                    verticalAlign: 'top',
                    style: {
                        fontSize: '15px',
                        fontWeight: 'bold'
                    }
                }
            }],
            data: data
        }],
        title: {
            text: 'Vendor Packages Sizes',
            align: 'left'
        },
        subtitle: {
            text: `Here you can see the size of each vendor package<br>PS: The size of your vendor is: ${vendorTotalSize}`,
            align: 'left'
        },
        tooltip: {
            useHTML: true,
            formatter: function() {
                return 'The size of <b>' + this.point.name + '</b> is <b>' + formatSizeUnits(this.point.value) + '</b>';
            }
        }
    });

            
    }


</script>
</html>
