Introduction
============

Many computing problems can be viewed as the evaluation of queries over data sources called *streams*. A stream is made of discrete data elements, called *events*; these can be as simple as a single number, or as complex as a special data structure with a large number of fields, a piece of text, or even a picture.

Event streams can be generated from a wide variety of sources. A small temperature sensor that is periodically queried by a system produces a stream of numerical values. A web server log saved to a file contains the latest content of a stream that records page requests. Even your monthly credit card statement is a stream of timestamped payments and expenses. The rate at which such streams produce events can vary widely: your personal credit card stream probably contains no more than a few entries per day; a sensor can emit a temperature reading once per minute, while a very busy web server may log entries thousands of times per second.

In most cases, these streams are not interesting by themselves.

## What is BeepBeep?

In this book, you will learn how to use the BeepBeep event stream query engine to perform various tasks over event streams of different nature.

BeepBeep has a few interesting features that distinguish it from other software systems based on events.

- It is **lightweight**. The core of BeepBeep is a stand-alone Java library that weighs less than 200 kilobytes (yes, that's *kilobytes*). BeepBeep also has low memory requirements; typically, as long as a Java virtual machine is available on a platform, BeepBeep can be made to run on it. It has been used in various environments, ranging from server clusters to smartphones and small devices such as the Raspberry Pi.
- It requires **zero configuration**. To start using BeepBeep, one simply needs to download the library and use the classes it provides in any Java program. Writing a working chain of processors (the basic computing units in BeepBeep) can be done in a few lines of code.
- It **does not force you to use a query language**. Many other event stream processing systems require you to write queries in some made up language that vaguely looks like SQL. In contrast, BeepBeep allows you to create, configure and pipe processor objects directly. As a result, the computation that is being executed is very close to your own mental model of what's happening. (And if you do want to use a query language, it also allows you to create your own.)
- It is **modular**. Apart from its small core of basic processors and functions, all other features of BeepBeep are bundled into a large number of optional plug-ins called *palettes*. This is in contrast with many other systems that attempt to provide a huge, monolithic, one-size-fits-all set of functionalities. In BeepBeep, you only use the palettes you need, resulting in a system that carries far less dead code.
- It is **versatile**. There are palettes to read Excel spreadsheets, parse Apache server logs, perform data mining, calculate statistics, analyze network packets, draw plots, and more. Among the weirdest palettes developed for BeepBeep, one even allows two smartphones to exchange data streams using their onboard camera and QR codes. As long as you can model a problem as a form of computation over streams, there is probably a way to do it with BeepBeep.
- It is heavily **customizable**. In case none of the existing palettes suit your needs, you can easily create your own processors, functions and events --typically in just a few lines of code. These custom-made objects can interact with all the others, meaning you only need to code what is missing, instead of reinventing the wheel.

## What BeepBeep is not

Although BeepBeep has a host of interesting features, it is not a panacea. There are other things for which it is not so good, or that have been purposefully excluded from its design.

- It is not a **distributed computing environment**. Although you can easily pass events around across machines using special network palettes, this is a far cry from what elaborate fault-tolerant publish-subscribe dispatching systems can provide you.
- It is not a **high-performance computing environment**. There are lots of things you can do reasonably well in BeepBeep, and numerous use cases for which it is more than fast enough. However, if you expect to crunch data at speeds of exabytes per second, chances are BeepBeep will be too sluggish to your taste.

However, if these limitations are OK for you, BeepBeep can prove an easy and fun tool to experiment with event stream processing.

## Getting Started

BeepBeep is free and open source software, distributed under the Lesser General Public License (LGPL). This means that it can be used free of charge, and even be included as a library inside commercial software.

In this chapter, you will learn to setup a programming environment in which you can use BeepBeep and run the code examples found throughout this book. The setup instructions use the [Eclipse](https://eclipse.org) integrated development environment (IDE), but they can easily transfer to other IDEs, or even to a command line-only installation. BeepBeep has very low system requirements, so anything from a Raspberry Pi to a supercomputer should be OK to run all the code examples from this book.

The first step is to open an Eclipse workspace, and to create a new empty Java project. BeepBeep must then be downloaded and included into the project. Pre-compiled releases of BeepBeep can be downloaded directly from BeepBeep's GitHub repository (`https://github.com/liflab/beepbeep-3`), under the *Releases* page. Official releases are stable and well-tested, although the API between releases (especially the old ones) can change a little. As a rule, there is no good reason not to use the latest release when starting a project.

BeepBeep is made of a single Java archive (JAR) file, called `beepbeep-3.jar`. This file is runnable and stand-alone, or can be used as a library, so it can be moved around to the location of your choice. If you want to create a Java project that uses BeepBeep, simply include `beepbeep-3.jar` in your CLASSPATH and you are good to go. In Eclipse, this means opening the *Build Path* dialog, selecting *Add external JARs*, and pointing to the location of `beepbeep-3.jar` on your machine.

To make sure that everything works, create a new Java class with a `main` method, and type the following:

``` java
import ca.uqac.lif.cep.*;
public class Test {
  public static void main(String[] args) {
    Source q = new QueueSource("foo");
    System.out.println(q.getPullableOutput().pull());
  }
}
```

This program creates a new instance of a `QueueSource` object, and pulls one event from its output. If everything compiles, and running the program prints a single line with the text `foo`, then your environment is correctly setup to use BeepBeep.

Palettes are additional JAR files that provide complementary functionalities to BeepBeep. Most of the palettes that will be used in this book can be downloaded from a sibling palette repository, located at `https://github.com/liflab/beepbeep-3-palettes`. The *Releases* page of this repository offers a large zip file, inside which each individual palette is a single JAR file. Palettes can be loaded into a project in the same way as BeepBeep's main JAR file. Note that palettes are not stand-alone: your project still requires `beepbeep-3.jar` even if you include palettes into it. For this reason, palettes are also sensitive to the version of the main JAR that you are using; attempting to load a palette compiled for an older version of BeepBeep may create errors, and vice versa. You should not experience these problems if you use the latest versions.

## Code examples and exercises

Most of the code examples in this book are also available online in a single big project. This project can be downloaded from GitHub at `https://github.com/liflab/beepbeep-3-examples`. The project contains an extensive Javadoc documentation of every file, which can be explored online at `https://liflab.github.io/beepbeep-3-examples`.

At the end of each main section, a few coding exercises are also suggested. These exercises require you to create chains of processors performing specific tasks. Writing an exercise all by yourself, and moving on to the next one, would be a bit pointless. However, you can assess whether you solved an exercise correctly by running it into a self-grading program called the **tutor**.

The program can be downloaded from `https://github.com/liflab/beepbeep-3-tutor`. It comes in the form of a single file, called `beepbeep-3-tutor.jar`, which you can include in your project like all the other JARs we talked about earlier. This library exposes an object called `Tutor`. Each of the book's exercises has a unique name; for example, exercise number 2 of Chapter 2 is called `C2E2`. There exists one tutor instance for each exercise; you can get the instance of your choice through `Tutor`'s static method `get()`.

In order to check the tutor setup, we can ask a dummy `Tutor` object for an exercise named `TEST`:

``` java
Tutor tutor = Tutor.get("TEST");
```

The correct answer to this exercise is a single `Processor` object that lets all events pass through; this is done by the aptly named `Passthrough` processor. To let the tutor check our answer, we have to tell it what are the inputs and the outputs of this processor chain:

``` java
Passthrough pt = new Passthrough();
tutor.setInput(pt).setOutput(pt);
```

The tutor feeds events through the input of our chain of processors, and observes what comes out of the output. We can then ask the tutor to check our solution through method `check`:

``` java
tutor.check();
```

If we run our program, after some time, the tutor should print at the terminal:

    Looks like everything is OK!

Let us modify our answer a little, and give the tutor an incorrect chain of processors, such as one that discards the first input event. This is done by the `Trim` processor.

``` java
Trim tr = new Trim(1);
tutor.setInput(tr).setOutput(tr);
tutor.check();
```

Running this programm will produce an output like this:

    I found an error in your solution.
    * With the input trace "A", "B", ...
      I got the output "B" at position 0
      I expected "A".

This indicates that the tutor found an input stream for which the output does not match what is expected of the correct solution. Here, since the `Trim` processor discards the first event it receives, the first event to be output is the letter "B" instead of the expected "A".

As you can see, the tutor can be a useful way to get your answers checked automatically. More will be said about the tutor in the first exercise section.

## Building BeepBeep

Instead of using a precompiled release, you may want to build BeepBeep directly from the sources, giving you access to the very latest features. First make sure you have the following installed:

- The Java Development Kit (JDK) to compile. BeepBeep is developed to comply with Java version 6; it is probably safe to use any later version.
- [Ant](http://ant.apache.org) to automate the compilation and build process

Download the sources for BeepBeep from [GitHub](https://github.com/liflab/beepbeep-3) or clone the
repository using Git:

    git@github.com:liflab/beepbeep-3.git

The project has a few dependencies; you can automatically download any libraries missing from your system by typing:

    ant download-deps

This will put the missing JAR files in the `deps` folder in the project's root. Compile the sources by simply typing:

    ant

This will produce a file called `beepbeep-3.jar` (or another library, depending on what you are compiling) in the folder. In addition, the script generates in the `doc` folder the Javadoc documentation for using BeepBeep.

BeepBeep can also test itself by running:

    ant test

Unit tests are run with [jUnit](http://junit.org); a detailed report of
these tests in HTML format is availble in the folder `tests/junit`, which
is automatically created. Code coverage is also computed with
[JaCoCo](http://www.eclemma.org/jacoco/); a detailed report is available
in the folder `tests/coverage`.

<!-- :wrap=soft: -->