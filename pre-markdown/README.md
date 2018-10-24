Introduction
============

Many computing problems can be viewed as the evaluation of queries over data sources called <!--\index{stream} \emph{streams}-->*streams*<!--/i-->. A stream is made of discrete data elements, called <!--\index{event} \emph{events}-->*events*<!--/i-->; these can be as simple as a single number, or as complex as a special data structure with a large number of fields, a piece of text, or even a picture.

Event streams can be generated from a wide variety of sources. A small temperature sensor that is periodically queried by a system produces a stream of numerical values. A web server log saved to a file contains the latest content of a stream where page requests are recorded. Even your monthly credit card statement is a stream of timestamped payments and expenses. The rate at which such streams produce events can vary widely: your personal credit card stream probably contains no more than a few entries per day; a sensor can emit a temperature reading once per minute, while a very busy web server may log entries thousands of times per second.

In most cases, these streams are not interesting by themselves. Rather, we are more likely to extract various kinds of information from them in order to answer questions about their content. What is the maximum temperature reading over the last day? Have I ever bought something at the grocery store two days in a row? How many times a certain page has been requested this week? Like the streams they refer to, the answer to these questions can be a single number, an interval, a table, a plot, or anything else. Computing the answer to these questions, in a nutshell, is the heart of stream processing.

Stream processing can be found in an extremely wide range of applications, but it is not always named as such. For example, observing the behaviour of a program for testing purposes is often called <!--\index{runtime verification} \emph{runtime verification}-->*runtime verification*<!--/i-->, yet the sequence of observations made on the program at various moments in time fits the definition of an event stream very well. Amplifying a feed of raw audio samples can also be seen as a very specific form of event stream processing, although an audio technician would probably never think about it in this way. Very often, a stream is analyzed and transformed on-the-fly, but this is not even a requirement: hence, reading a pre-recorded sequence of events from some static source also counts as stream processing.

## Computations Over Streams

At the onset, event stream processing is like any normal programming activity. Given an input stream, one can write a script or a program in the language of one's choice to perform the desired computation. However, certain hypotheses make event stream processing more complex than simple scripting.

1. As its name implies, the source of an event stream processor is a *stream*. This means that data elements arrive progressively, one event at a time. Accessing these event feeds is often more complex than simply opening a file or connecting to a database.
2. We typically expect an answer to be produced as soon as it can be known; this is called <!--\index{online processing} \emph{online}-->*online*<!--/i--> processing. For example, if we want to calculate the average temperature on a window of the past 10 readings, the output value should be computed as soon as those 10 readings have been received. This "streaming" mode of operation is to be contrasted with an <!--\index{offline processing} \emph{offline}-->*offline*<!--/i--> or "batch" mode, where results for all windows of 10 events would be computed and output all at once at the end of the program.
3. A stream unfolds in only one direction: forward. It is generally not possible for a stream processor to rewind an input stream and read previous events a second time. If something must be remembered about the past, it is up to the stream processor to store it somewhere.

Guarantees on the delivery of events in a CEP system can also vary. “At most once” delivery entails that every event may be sent to its intended recipient, but may also be lost. “At least once” delivery ensures reception of the event, but at the potential cost of duplication, which must then be handled by the receiver. In between is perfect event delivery, where reception of each event is guaranteed without duplication. These concepts generally matter only for distributed event processing systems, where communication links between nodes may involve loss and latency.

Some of these hypotheses can sometimes be modified. For example, if one is reading a stream from a pre-recorded file, it is indeed possible to move backwards and return to previous events, contrary to what condition #3 stipulates. However, in the general case, processing a sequence of events in a streaming fashion is a little more involved than writing a generic piece of code.

Over the years, various tools, libraries and systems have been developed to help users process event streams. These tools and techniques can roughly be divided into two groups. The first group of software originates from the database community, and includes tools like <!--\index{Cayuga} Cayuga-->Cayuga<!--/i-->, <!--\index{Borealis} Borealis-->Borealis<!--/i-->, <!--\index{TelegraphCQ} TelegraphCQ-->TelegraphCQ<!--/i-->,  <!--\index{Esper} Esper-->Esper<!--/i-->, <!--\index{LINQ} LINQ-->LINQ<!--/i-->, <!--\index{Siddhi} Siddhi-->Siddhi<!--/i-->, <!--\index{VoltDB} VoltDB-->VoltDB<!--/i--> and <!--\index{StreamBase SQL} StreamBase SQL-->StreamBase SQL<!--/i-->. While their input languages vary, most can best be seen as special cases of database query languages, with added support for computation of aggregate functions (average, minimum, etc.) over sliding windows of events (e.g. all events of the last minute). The second group of software, while not labelled specifically as such, comes from the runtime verification community. Indeed, runtime monitors such as <!--\index{JavaMOP} JavaMOP-->JavaMOP<!--/i-->, <!--\index{LARVA} LARVA-->LARVA<!--/i-->, <!--\index{LogFire} LogFire -->LogFire<!--/i-->, <!--\index{MarQ} MarQ-->MarQ<!--/i-->, <!--\index{MonPoly} MonPoly-->MonPoly<!--/i-->, <!--\index{Tracematches} Tracematches -->Tracematches<!--/i-->, <!--\index{TeSSLA} TeSSLA-->TeSSLA<!--/i-->, <!--\index{J-Lo} J-Lo-->J-Lo<!--/i-->, PQL, PTQL, <!--\index{SpoX} SpoX-->SpoX<!--/i--> and <!--\index{PoET} -->PoET<!--/i-->  are designed with the purpose of detecting violations of some sequential pattern of events generated by a system in real time.

It was observed in earlier work by the author of this book that these two classes of systems have complementary strengths. The handling of aggregate functions over events provided by CEP tools is notably lacking in virtually all existing runtime monitors. Conversely, monitors generally allow the expression of intricate sequential relationships between events, using finite-state automata or temporal languages, which go far beyond CEP's traditional capabilities.

## Why Use an Event Stream Processing System?

An organization may have multiple log repositories at its disposition: execution logs, server logs, and possibly other real-time sources of events. Useful information can be extracted from these logs, which often lies dormant, dispersed across file servers and databases.

A first, natural step to extract and process data consists of writing a bunch of quick crunching scripts in some mainstream programming language. To this end, <!--\index{Python} Python-->Python<!--/i-->, <!--\index{PHP} PHP-->PHP<!--/i--> or <!--\index{Perl} Perl-->Perl<!--/i--> can come in handy. However, as time goes by, a tiny script becomes two, which together grow from a few tens of lines to a few hundreds. More often than not, their content is so specific to the current data-crunching task that hardly anything they contain is worth reusing. Since every script is essentially single-use, not much time is spent on testing or documentation. The end result is a situation similar to the next figure, which shows a proliferation of hack-together, use-once, throw-away scripts.

{@img Scripts.png}{Processing logs with user-defined scripts.}{.6}

In contrast, an event stream processing system (such as BeepBeep) concentrates many recurring log processing tasks in a single location. Users still need to write scripts; however, these scripts can be expressed at a higher level of abstraction, by combining lower-level functions provided by the underlying system. This has for effect of improving their readability, but also of reducing their size. Most importantly, since the functionalities provided by the event stream processing system are intended to be generic and reusable, they are worth spending time to be well documented and tested. As a consequence, the same processing tasks can be accomplished in fewer lines of custom user code. This is what is illustrated in the next figure.

{@img Queries.png}{Processing logs with an event stream processing system.}{.6}

## What is BeepBeep?

In this book, you will learn how to use an event stream query engine called BeepBeep to perform various tasks over event streams of different nature. BeepBeep began as an academic research tool developed by the author of this book while he was a PhD student at Université du Québec à Montréal, Canada. Version 1 of the system was developed from 2008 to 2013 and has been the subject of numerous papers and case studies (see the *Further Reading* section at the end of this book). It was much more limited than the BeepBeep we are talking about in this book, and could only perform a specific kind of stream processing called *runtime verification*. The main distinguishing point of this first version was the handling of complex events with a nested structure (such as XML documents), and an input language that borrowed from a mathematical language called <!--\index{Linear Temporal Logic (LTL)} Linear Temporal Logic-->Linear Temporal Logic<!--/i-->. BeepBeep 1 is no longer under active development and is considered obsolete for all practical purposes. 

In 2013-2014, the version 2 was an attempt at implementing the same concepts as BeepBeep 3. It was cancelled at an early stage of development and was never officially released. One can hence consider BeepBeep 3 as the second "real" incarnation of BeepBeep. It benefits from a complete redesign of the platform, which includes and significantly extends most of the 1.x features.

BeepBeep has a few interesting features distinguishing it from other software systems based on events.

- It is **intuitive**. Virtually every computation in BeepBeep can be expressed in a totally graphical way, using a vast set of pictograms (most of which are detailed in an appendix at the end of this book). Therefore, one does not need to read through Java code to understand a program that uses BeepBeep.
- It is **lightweight**. The core of BeepBeep is a stand-alone Java library that weighs less than 200 kilobytes (yes, that's *kilobytes*). BeepBeep also has low memory requirements; typically, as long as a Java virtual machine is available on a platform, BeepBeep can be made to run on it. It has been used in various environments, ranging from server clusters to smartphones and small devices such as the Raspberry Pi.
- It requires **zero configuration**. To start using BeepBeep, one simply needs to download the library and use the classes it provides in any Java program. Writing a working chain of processors (the basic computing units in BeepBeep) can be done in a few lines of code.
- It **does not force users to use a query language**. Many other event stream processing systems require writing queries in some made-up language vaguely similar to SQL. In contrast, BeepBeep enables users to create, configure and **pipe** processor objects directly. As a result, the computation that is being executed is very close to users' own mental idea of what is happening. (And if users prefer to use a query language, it is possible to create their own; see Chapter 8.)
- It is **modular**. Apart from its small core of basic processors and functions, all other features of BeepBeep are bundled into a large number of optional plug-ins called *palettes*. This is different from many other systems that attempt to provide a huge, monolithic, one-size-fits-all set of functionalities. In BeepBeep, users only use the palettes they need, resulting in a system that carries far less dead code.
- It is **versatile**. There are palettes to read Excel spreadsheets, parse Apache server logs, perform data mining, calculate statistics, analyze network packets, draw plots, and more (see Chapter 6 for some examples). Among the most unusual palettes developed for BeepBeep, one even allows two smartphones to exchange data streams using their onboard camera and QR codes. As long as a problem can be modelled as a form of computation over streams, there is probably a way to do it with BeepBeep.
- It is heavily **customisable**. In case none of the existing palettes meet the users' needs, they can easily create their own processors, functions and events --typically in just a few lines of code (see Chapter 7). These custom-made objects can interact with all the others, meaning you only need to code what is missing, instead of reinventing the wheel.

Although BeepBeep has a host of interesting features, it is not a panacea. There are other things for which it is not as appropriate, or that have been purposefully excluded from its design:

- It is not a **distributed computing environment**. Although events can easily be passed around across machines using special network palettes, this is a far cry from what elaborate fault-tolerant publish-subscribe dispatching systems can provide.
- It is not a **high-performance computing environment**. Many things can be done reasonably well in BeepBeep, and there are several situations in which it is quite fast. However, if you expect to crunch data at speeds of exabytes per second, chances are BeepBeep will be too slow for your task.

However, if these limitations are not restrictive, BeepBeep can prove an easy and convivial tool to experiment with event stream processing.

## Getting Started

BeepBeep is a free and open source software, distributed under the Lesser General Public License (LGPL). Accordingly, its use is free of charge, and the tool may even be included as a library inside commercial software.

In this chapter, you will learn to set up a programming environment using BeepBeep to run the code examples found throughout this book. The set-up instructions use the [Eclipse](https://eclipse.org) integrated development environment (IDE), but they can easily transfer to other IDEs, or even to a command line-only installation. BeepBeep has very low system requirements, so anything from a Raspberry Pi to a supercomputer should be able to run all the code examples from this book.

The first step is to open an <!--\index{Eclipse (IDE)} Eclipse-->Eclipse<!--/i--> workspace, and to create a new empty Java project. BeepBeep must then be downloaded and included into the project. Pre-compiled releases of BeepBeep can be downloaded directly from BeepBeep's GitHub repository ([https://github.com/liflab/beepbeep-3](https://github.com/liflab/beepbeep-3)), under the *Releases* page. Official releases are stable and well-tested, although the API between releases (especially the old ones) can change slightly. As a rule, there is no good reason not to use the latest release when starting a project.

BeepBeep is made of a single Java archive (JAR) file, called `beepbeep-3.jar`. This file is runnable and stand-alone, or can be used as a library, so it can be moved around to the location of your choice. If you want to create a Java project that uses BeepBeep, simply include `beepbeep-3.jar` in your CLASSPATH and you are ready to begin. In Eclipse, this means opening the *Build Path* dialog, selecting *Add external JARs*, and pointing to the location of `beepbeep-3.jar` on your machine.

To make sure that everything works, create a new Java class with a `main()` method, and type the following:

{@snipm basic/HelloWorld.java}{/}

This program creates a new instance of a <!--\index{QueueSource@\texttt{QueueSource}} \texttt{QueueSource}-->`QueueSource`<!--/i--> object, and pulls one event from its output. If everything compiles, and running the program prints a single line with the text `foo`, then the environment is correctly setup to use BeepBeep.

*Palettes* are additional JAR files that provide complementary functionalities to BeepBeep. Most of the <!--\index{palettes} palettes-->palettes<!--/i--> that will be used in this book can be downloaded from a sibling palette repository, located at [https://github.com/liflab/beepbeep-3-palettes](https://github.com/liflab/beepbeep-3-palettes). The *Releases* page of this repository offers a large zip file, inside which each individual palette is a single JAR file. Palettes can be loaded into a project in the same way as BeepBeep's main JAR file. Note that palettes are not stand-alone: your project still requires `beepbeep-3.jar` even if palettes are included into it. For this reason, palettes are also sensitive to the version of the main JAR that you are using; attempting to load a palette compiled for an older version of BeepBeep may create errors, and vice versa. No problems should occur if the latest versions are used.

## How to Read This Book

The first part of this book (chapters 2 to 5) is organized in a roughly linear fashion: each chapter builds on notions that have been covered in the previous one.

- Chapter 2 describes the very basic concepts of BeepBeep's operation: streams, pipes, processors, pushing and pulling events, and composition. You will not understand anything of the rest of this book before first going through this chapter!
- Chapter 3 describes the general-purpose `Function` and `Processor` objects that are provided in the system's core. You will learn how to trim, filter and slice event streams, apply functions and sliding windows to events, and so on. Virtually any BeepBeep program involves one of the objects described in this chapter.
- Chapter 4 describes some more functions and processors specific to particular use cases, such as processing character strings or manipulating collections of objects. It also gives more details about more technical features of `Processor` objects, such as how to copy them, or call their functions across multiple threads.
- Chapter 5 leaves BeepBeep's core, and describes the functionalities provided by a standard set of palettes that have been developed alongside the main software. Not all palettes may be interesting to you, so each section of this chapter is written so as to be relatively independent of the others.

The second part of the book (chapters 6 to 8) is made of independent chapters covering other aspects of BeepBeep.

- Chapter 6 mixes all the content of the previous chapters together, and shows a number of more complex use cases that illustrate the capabilities of BeepBeep and its standard palettes. You will learn how BeepBeep can be used to perform runtime monitoring in a video game, process telemetry from a space probe, or analyze the power consumption of home appliances, among other things.
- Chapter 7 is intended for BeepBeep developers. It shows how Java programmers can easily create their own `Processor` and `Function` objects, package them into their own palette, and make them interact with other BeepBeep objects.
- Chapter 8 concentrates on one particular BeepBeep palette, called *DSL*. Rather than piping processors directly using Java, this palette makes it possible for end-users to define the syntax of a custom language, and to write an *interpreter* that builds processor chains automatically from expressions of that language.

Finally, the book ends with a few appendices that are meant as a reference.

- Appendix A defines the broad guidelines for drawing processor chains similar to the illustrations shown throughout this book.
- Appendix B is an illustrated glossary listing all the `Processor` and `Function` objects provided by BeepBeep and its palettes, and which are mentioned somewhere in the book. For each of them, it shows the standard picture used to represent them and provides a short definition.
- Appendix C is a list of references to books and scientific papers providing more details about some of the topics discussed in this book.

## Code Examples and Exercises

Most of the code examples in this book are also available online in a single big project. This project can be downloaded from <!--\index{GitHub} GitHub-->GitHub<!--/i--> at [https://github.com/liflab/beepbeep-3-examples](https://github.com/liflab/beepbeep-3-examples). It contains an extensive Javadoc documentation of every file, which can be explored online at [https://liflab.github.io/beepbeep-3-examples](https://liflab.github.io/beepbeep-3-examples).

When a code snippet is followed by the ⚓ symbol, this indicates that this piece of code is also available online in the code example repository. When viewing an electronic version of this book (such as an online website or a PDF), the ⚓ symbol is actually a hyperlink leading directly to the first line of that snippet in the GitHub repository. As an example, try clicking on the link corresponding to the following code block:

{@snipm basic/QueueSourceUsage.java}{/}

We can also notice that the online version of the code is sometimes interspersed with comment lines that are absent from the book examples. This is done to improve the legibility of the examples, given that they are already discussed at length in the text itself.

At the end of each main section, a few coding exercises are also suggested. These exercises require the creation of chains of processors performing specific tasks. Writing an exercise all by yourself, and moving on to the next one, would be a bit pointless. It is possible to determine whether exercises have been done correctly by testing them into a self-grading program called the **tutor**.

The program can be downloaded from [https://github.com/liflab/beepbeep-3-tutor](https://github.com/liflab/beepbeep-3-tutor). It comes in the form of a single file, called `beepbeep-3-tutor.jar`, which can be integrated in a project like all the other JARs mentioned earlier. This library exposes an object called <!--\index{tutor} \texttt{Tutor}-->`Tutor`<!--/i-->. All exercises in the book have a unique name; for example, exercise number 2 of Chapter 2 is called `C2E2`. There exist tutor instances for many exercises; you can get the instance of your choice through `Tutor`'s static method `get()`. If a tutor does not exist for an exercise, an exception will be thrown. (At the time of this writing, the tutor is still a work in progress.)

In order to check the tutor setup, it is possible to request a dummy `Tutor` object for an exercise named `TEST`:

``` java
Tutor tutor = Tutor.get("TEST");
```

The correct answer to this exercise is a single `Processor` object that lets all events pass through; this is done by the aptly named `Passthrough` processor. To let the tutor check the answer, it has to be told what are the inputs and the outputs of this processor chain:

``` java
Passthrough pt = new Passthrough();
tutor.setInput(pt).setOutput(pt);
```

The tutor feeds events through the input of the chain of processors, and observes what comes out of the output. The tutor can then be asked to check the solution through method `check`:

``` java
tutor.check();
```

By running the program, after some time, the tutor should print at the terminal:

    Looks like everything is OK!

To show what happens when a solution is incorrect, we shall now give the tutor a modified chain of processors, which discards the first input event. This is done by the <!--\index{Trim@\texttt{Trim}} \texttt{Trim}-->`Trim`<!--/i--> processor.

``` java
Trim tr = new Trim(1);
tutor.setInput(tr).setOutput(tr);
tutor.check();
```

Running this program will produce an output as the following:

    I found an error in your solution.
    * With the input trace "A", "B", ...
      I got the output "B" at position 0
      I expected "A".

This indicates that the tutor found an input stream for which the output does not match what is expected of the correct solution. Here, since the `Trim` processor discards the first event it receives, the first event to be output is the letter "B" instead of the expected "A".

## Building BeepBeep

Instead of using a pre-compiled release, users may want to build BeepBeep directly from the sources, thus giving access to the very latest features. First make sure the following has been installed:

- The Java Development Kit (JDK) to compile. BeepBeep is developed to comply with Java version 6; it is probably safe to use any later version.
- [Ant](http://ant.apache.org) to automate the <!--\index{Ant} compilation-->compilation<!--/i--> and build process

Download the sources for BeepBeep from [GitHub](https://github.com/liflab/beepbeep-3) or clone the repository using <!--\index{GitHub} Git-->Git<!--/i-->:

    git@github.com:liflab/beepbeep-3.git

The project has a few dependencies; any libraries missing from the system can be automatically downloaded by typing:

    ant download-deps

This will put the missing JAR files in the `deps` folder in the project's root. The sources can be compiled by simply typing:

    ant

This will produce a file called `beepbeep-3.jar` in the folder. In addition, the script generates in the `doc` folder the Javadoc documentation for using BeepBeep.

BeepBeep can also test itself by running:

    ant test

Unit tests are run with [jUnit](http://junit.org); a detailed report of these tests in HTML format is available in the folder `tests/junit`, which is automatically created. Code coverage is also computed with [JaCoCo](http://www.eclemma.org/jacoco/); a detailed report is available in the folder `tests/coverage`.

For the sake of clarity, we give below the hashes of the latest commits on the various GitHub repositories containing BeepBeep code and examples. All the examples in this book are based on the software in the state it was when these commits were pushed:

- BeepBeep core: `4686bca5d7d165f6287b0aec209e582171f7f67e`
- BeepBeep palettes: `f443afa16ac19858f251484324cb69a50d28b0f1`
- Code examples: `07f79a03aded9802074ba92982c381cddb690444`

## Acknowledgements

This work was done thanks to the financial support of the Natural Sciences and Engineering Research Council of Canada (NSERC) and the Canada Research Chair on Software Specification, Testing and Validation.

<!-- :wrap=soft: -->