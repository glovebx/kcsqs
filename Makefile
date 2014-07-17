# Makefile for kcsqs
CC=gcc
CFLAGS=-Wl,-rpath,/usr/local/libevent-2.0.21-stable/lib/:/usr/local/kyotocabinet-1.2.76/lib/ -L/usr/local/libevent-2.0.21-stable/lib/ -levent -L/usr/local/kyotocabinet-1.2.76/lib/ -lkyotocabinet -I/usr/local/libevent-2.0.21-stable/include/ -I/usr/local/kyotocabinet-1.2.76/include/ -lz -lbz2 -lrt -lpthread -lm -lc -O2 -g

kcsqs: kcsqs.c
	$(CC) -o kcsqs kcsqs.c prename.c $(CFLAGS)
	@echo ""
	@echo "kcsqs build complete."
	@echo ""	

clean: kcsqs
	rm -f kcsqs

install: kcsqs
	install $(INSTALL_FLAGS) -m 4755 -o root kcsqs $(DESTDIR)/usr/bin
