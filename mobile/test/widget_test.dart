import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:insyte_crm_mobile/features/home/home_screen.dart';

void main() {
  testWidgets('Home screen shows title', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(home: HomeScreen()),
    );

    expect(find.text('InSyte CRM'), findsOneWidget);
    expect(find.text('Mobile app ready'), findsOneWidget);
  });
}
