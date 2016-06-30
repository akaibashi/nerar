CXX = g++ -Wall -O2
CC = gcc -Wall -O2
CXXFLAGS = `pkg-config --cflags opencv`
LDFLAGS = `pkg-config --libs opencv`

.cpp:
	$(CXX) $@.cpp $(CXXFLAGS) $(LDFLAGS) $(FRAMEWOKS) -o $@

.c:
	$(CC) $@.c $(CXXFLAGS) $(LDFLAGS) $(FRAMEWOKS) -o $@
