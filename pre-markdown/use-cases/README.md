A few use cases
===============

## Voyager telemetry

In this section, we study the data produced by the *Voyager 2* space probe. This automatic probe was launched by NASA in 1977 on a trajectory that allowed it to fly close to four planets of the solar system: Jupiter, Saturn, Uranus and Neptune. On this "Grand Tour" of the solar system, Voyager 2 (along with its twin, Voyager 1) collected scientific data and snapped pictures that greatly expanded our knowledge of the gas giants and their moons.

At the time of this writing, both Voyagers are still operational, and currently explore the outer edge of the solar system. The telemetry sent back by these probes, going all the way back to 1977, is publicly available in the form of various text files on a NASA FTP archive. In our example, we shall use a simple, collated dataset that can be downloaded from the following URL:

    ftp://spdf.gsfc.nasa.gov/pub/data/voyager/voyager2/merged/ 

The files contained in that repository are named `vy2_YYYY.asc`, where `YYYY` corresponds to a year. These files provide averaged hourly readings of various instruments in the spacecraft. One line of such a file looks like this:

```
1977 365 22   1.91    0.6    1.2   ...
```

A file that accompanies the repository describes the meaning of each column. For the purpose of this example, we are only interested in the first four columns, which respectively represent the year, decimal day, hour (0-23) and spacecraft's distance to the Sun expressed in Astronomical Units (AU). From this data, let us see if we can detect the **planetary encounters** of Voyager 2, by looking at how its speed changes over time.

Our long processor chain can be broken into three parts: pre-processing, processing, and visualization.

### Pre-processing

Pre-processing is the part where we start from the raw data, and format it so that the actual computations are then possible. In a nutshell, the pre-processing step amounts to the following processor chain:

{@img doc-files/voyager/pre-processing.png}{Pre-processing the Voyager data.}{.6}

Since the data is split into multiple CSV files, we shall first create one instance of the <!--\index{ReadLines@\texttt{ReadLines}} \texttt{ReadLines}-->`ReadLines`<!--/i--> processor for each file, and put these `Source`s into an array. We can then pass this to a processor called <!--\index{Splice@\texttt{Splice}} \texttt{Splice}-->`Splice`<!--/i-->, which is the first processor box shown in the previous picture. The splice pulls events from the first source it is given, until that source does not yield any new event. It then starts pulling events from the second one, and so on. This way, the contents of the multiple text files we have can be used as an uninterrupted stream of events. This is why the pictogram for `Splice` is a small bottle of glue.

We then perform a drastic reduction of the data stream. The input files have hourly readings, which is a degree of precision that is not necessary for our purpose. We keep only one reading per week, by applying a `CountDecimate` that keeps one event every 168 (there are 168 hours in a week). Moreover, the file corresponding to year 1977 has no meaningful data before week 31 or so (the launch date); we ignore the first 31 events of the resulting stream by using a `Trim`. Finally, as a last pre-processing step, we convert plain text events into arrays by splitting each string on spaces. This is done by applying the <!--\index{Strings@\texttt{Strings}!SplitString@\texttt{SplitString}} \texttt{SplitString}-->`SplitString`<!--/i--> function. The Java code of this first pre-processing step looks like this:

{@snipm voyager/PlotSpeed.java}{/}

### Processing



{@img doc-files/voyager/processing.png}{Processing the Voyager data.}{.6}

### Visualization

{@img doc-files/voyager/visualization.png}{Visualizing the Voyager data.}{.6}

One can see that the last three peaks correspond precisely to the dates of Voyager's flybys of Jupiter, Saturn, and Neptune: 


<!-- :wrap=soft: -->