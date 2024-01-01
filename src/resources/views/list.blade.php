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
            <span class="badge badge-warning">Old</span>
            <span class="badge badge-danger">Very Old</span>
        </div>

        <table class="table table-striped" id="packages-table">
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
                <tr>
                    <td colspan="7" class="text-center">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>

    </div>
    <div id="chart_div" style="width: 100%; height: 1000px;"></div>
</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script>
    // get data for the table
    $.ajax({
        url: "{{ route('package-checker.get-installed-packages') }}",
        method: 'GET',
        success: function(response) {
            let table = $('#packages-table');
            // remove loading message
            table.find('tbody tr').remove();
            response.forEach(function(item) {
                // fill the table
                let row = `<tr>
                    <td>${item.name}</td>
                    <td class="package-requirements" data-package-name="${item.name}" data-package-version="${item.version}">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </td>
                    <td>${item.version}</td>
                    <td class="package-latest-version" data-package-name="${item.name}">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>    
                    </td>
                    <td class="package-status" data-package-name="${item.name}">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </td>
                    <td class="package-release-date" data-package-name="${item.name}">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </td>
                    <td class="package-size" data-package-name="${item.name}" >
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </td>
                </tr>`;
                table.append(row);
            });
            getPackagesDetails();

        }
    });


    function getPackagesDetails()
    {
        jQuery('.package-requirements').each(function() {
            var packageName = jQuery(this).data('package-name');
            var packageVersion = jQuery(this).data('package-version');

            var packageRequirements = jQuery(this);
            var packageStatus = jQuery('.package-status[data-package-name="' + packageName + '"]');
            var packageReleaseDate = jQuery('.package-release-date[data-package-name="' + packageName + '"]');
            var packageSize = jQuery('.package-size[data-package-name="' + packageName + '"]');
            var packageLatestVersion = jQuery('.package-latest-version[data-package-name="' + packageName + '"]');

            jQuery.ajax({
                url: "{{ route('package-checker.get-package-details') }}", // "http://localhost:8000/package-checker/get-requirements/?name=" + packageName,
                method: 'GET',
                // async: false,
                data: {
                    name: packageName,
                    version: packageVersion,
                },
                success: function(response) {
                    // fill status, release date and requirements
                    // release date
                    packageRequirements.html('');
                    if (response.requirements.length === 0) {
                        packageRequirements.append('<span class="badge badge-secondary">No known requirements</span>');
                    } else {
                        let requirements = '';
                        response.requirements.forEach(function(requirement) {
                            requirements += `<span class="badge badge-primary mr-1">${requirement}</span>`;
                        });
                        packageRequirements.append(requirements);
                        // append requirements to package-size as json
                        packageSize.attr('data-package-requirements', JSON.stringify(response.requirements));
                    }


                    // status
                    packageStatus.html('');
                    if (response.status === 'good') {
                        packageStatus.append('<span class="badge badge-success">Good</span>');
                    } else if (response.status === 'old') {
                        packageStatus.append('<span class="badge badge-warning">Old</span>');
                    } else if (response.status === 'very_old') {
                        packageStatus.append('<span class="badge badge-danger">Very Old</span>');
                    } else {
                        packageStatus.append(response.status);
                    }

                    // release date
                    packageReleaseDate.html('');
                    packageReleaseDate.append(response.release_date);

                    // latest version
                    packageLatestVersion.html('');
                    packageLatestVersion.append(response.latest_version);

                    getVendorSize(packageName);
                }
            });
        });
    }

    function getVendorSize(packageName)
    {
        let packageSize = jQuery('.package-size[data-package-name="' + packageName + '"]');
        let packageRequirements = packageSize.data('package-requirements');
        let payload = {
            name: packageName,
            requirements: packageRequirements,
        };
        jQuery.ajax({
            url: "{{ route('package-checker.get-size') }}", // "http://localhost:8000/package-checker/get-size/?name=" + packageName,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: payload,
            success: function(response) {
                packageSize.html(response);
            }
        });
    }


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
